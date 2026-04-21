<?php
/**
 * Lightweight spreadsheet reader with fallback support.
 * - Uses PhpSpreadsheet when available and complete.
 * - Falls back to native .xlsx XML parsing.
 * - Supports .csv natively.
 */

if (!function_exists('loadSpreadsheetRows')) {
    function loadSpreadsheetRows($tmpPath, $originalName) {
        $ext = strtolower(pathinfo((string)$originalName, PATHINFO_EXTENSION));

        // Try PhpSpreadsheet first when class set is complete.
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        if (
            class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory') &&
            class_exists('\\PhpOffice\\PhpSpreadsheet\\ReferenceHelper')
        ) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
                return $spreadsheet->getActiveSheet()->toArray();
            } catch (\Throwable $e) {
                // Continue to fallback readers below.
            }
        }

        if ($ext === 'xlsx') {
            return loadXlsxRowsNative($tmpPath);
        }

        if ($ext === 'csv') {
            return loadCsvRowsNative($tmpPath);
        }

        if ($ext === 'xls') {
            throw new \RuntimeException("Legacy .xls requires complete PhpSpreadsheet installation. Use .xlsx or .csv for now.");
        }

        throw new \RuntimeException("Unsupported file type. Upload .xlsx or .csv.");
    }
}

if (!function_exists('loadCsvRowsNative')) {
    function loadCsvRowsNative($tmpPath) {
        $rows = [];
        $handle = fopen($tmpPath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open CSV file.');
        }

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;
        }

        fclose($handle);
        return $rows;
    }
}

if (!function_exists('loadXlsxRowsNative')) {
    function loadXlsxRowsNative($tmpPath) {
        if (!class_exists('\\ZipArchive')) {
            throw new \RuntimeException(
                "XLSX import requires PHP ZIP extension (ZipArchive). Enable php_zip in XAMPP or upload CSV instead."
            );
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            throw new \RuntimeException('Unable to open .xlsx file.');
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new \RuntimeException('Invalid .xlsx: worksheet not found.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sharedStrings = parseSharedStrings($sharedXml);
        }

        $zip->close();

        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false) {
            throw new \RuntimeException('Invalid .xlsx worksheet XML.');
        }

        $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xmlRows = $sheet->xpath('//x:sheetData/x:row');

        $rows = [];
        foreach ($xmlRows as $xmlRow) {
            $rowData = [];
            $maxCol = -1;

            foreach ($xmlRow->c as $cell) {
                $ref = (string)$cell['r'];
                if (!preg_match('/^([A-Z]+)[0-9]+$/', $ref, $m)) {
                    continue;
                }

                $colIndex = columnLettersToIndex($m[1]);
                if ($colIndex > $maxCol) {
                    $maxCol = $colIndex;
                }

                $type = (string)$cell['t'];
                $value = '';

                if ($type === 'inlineStr') {
                    $value = (string)$cell->is->t;
                } else {
                    $raw = isset($cell->v) ? (string)$cell->v : '';
                    if ($type === 's') {
                        $idx = (int)$raw;
                        $value = $sharedStrings[$idx] ?? '';
                    } else {
                        $value = $raw;
                    }
                }

                $rowData[$colIndex] = $value;
            }

            if ($maxCol >= 0) {
                $normalized = array_fill(0, $maxCol + 1, '');
                foreach ($rowData as $i => $val) {
                    $normalized[$i] = $val;
                }
                $rows[] = $normalized;
            }
        }

        return $rows;
    }
}

if (!function_exists('parseSharedStrings')) {
    function parseSharedStrings($xml) {
        $sst = @simplexml_load_string($xml);
        if ($sst === false) {
            return [];
        }

        $strings = [];
        $sst->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $items = $sst->xpath('//x:si');
        if ($items === false) {
            return [];
        }

        foreach ($items as $si) {
            $textNodes = $si->xpath('.//*[local-name()="t"]');
            if ($textNodes === false || empty($textNodes)) {
                $strings[] = '';
                continue;
            }

            $composed = '';
            foreach ($textNodes as $tNode) {
                $composed .= (string)$tNode;
            }
            $strings[] = $composed;
        }

        return $strings;
    }
}

if (!function_exists('columnLettersToIndex')) {
    function columnLettersToIndex($letters) {
        $letters = strtoupper($letters);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }
}
