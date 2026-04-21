<?php
$pageTitle = "Student RFID Scanner";
$hideLayout = true;
include 'includes/header.php';
?>

<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <img src="isu-logo.png" alt="ISU Logo" class="login-logo" onerror="this.style.display='none'">
            <h2 style="font-weight: 700; color: var(--text-main); font-size: 1.75rem;">Student Voucher Scanner</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Public RFID interface</p>
        </div>

        <div id="rfidScanView">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem; animation: pulse 2s infinite;">📱</div>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Tap your RFID card</p>
            </div>

            <div class="form-group">
                <label for="rfidTag" class="form-label">RFID Tag</label>
                <div style="position: relative;">
                    <i class="fas fa-id-card" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="rfidTag" class="form-control" placeholder="Scan card..." style="padding-left: 2.75rem;" autocomplete="off">
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <button type="button" class="btn btn-primary" id="scanBtn" onclick="processRFIDScan()" style="width: 100%; justify-content: center; height: 50px; font-size: 1rem;">
                    <i class="fas fa-search"></i> Scan for Voucher
                </button>
            </div>

            <div id="alertContainer" style="margin-top: 1.5rem;"></div>
        </div>

        <div id="rfidResultView" style="display: none;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div id="resultIcon" style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <h3 id="resultMessage" style="color: var(--text-main); margin: 0;">Voucher Result</h3>
            </div>

            <div id="studentCardDisplay" style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; display: none;">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <img id="studentPhoto" src="" alt="Student Photo" style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover; background: white;">
                    <div style="flex-grow: 1;">
                        <h4 id="studentName" style="color: var(--text-main); margin: 0 0 0.5rem 0;"></h4>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">ID: <strong id="studentId"></strong></p>
                    </div>
                </div>
            </div>

            <div id="voucherCardDisplay" style="background: rgba(72, 187, 120, 0.1); padding: 1.5rem; border-radius: var(--radius-sm); border: 2px solid rgba(72, 187, 120, 0.3); margin-bottom: 1.5rem; display: none;">
                <h4 style="color: #22863a; margin: 0 0 1rem 0;">Your Voucher</h4>
                <div style="font-family: 'Courier New', monospace; font-size: 1.3rem; font-weight: 700; color: #22863a; background: white; padding: 1rem; border-radius: 6px; text-align: center; margin-bottom: 1rem; border: 2px dashed #48bb78;" id="voucherCode"></div>
                <div style="display: grid; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span style="color: #22863a;">Validity:</span>
                        <strong style="color: #22863a;" id="voucherMinutes"></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span style="color: #22863a;">Department:</span>
                        <strong style="color: #22863a;" id="voucherDept"></strong>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" onclick="resetRFIDForm()" style="width: 100%; justify-content: center; height: 50px; font-size: 1rem;">
                <i class="fas fa-redo"></i> Scan Another
            </button>
        </div>

        <div style="margin-top: 2rem; text-align: center;">
            <p style="font-size: 0.85rem; color: var(--text-muted);">
                <a href="../index.html" style="color: var(--primary-color); text-decoration: none;">Back to Home</a>
            </p>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
</style>

<script>
const rfidInput = document.getElementById('rfidTag');
const scanBtn = document.getElementById('scanBtn');
const alertContainer = document.getElementById('alertContainer');
const scanView = document.getElementById('rfidScanView');
const resultView = document.getElementById('rfidResultView');

if (rfidInput) {
    rfidInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processRFIDScan();
        }
    });
}

async function processRFIDScan() {
    const rfid = (rfidInput.value || '').trim();
    if (!rfid) {
        showAlert('Please scan an RFID tag.', 'error');
        return;
    }

    scanBtn.disabled = true;
    scanBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    alertContainer.innerHTML = '';

    try {
        const formData = new FormData();
        formData.append('rfid', rfid);

        const response = await fetch('api/v1/scan.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.status === 'success') {
            handleResult('success', result);
        } else if (result.status === 'warning') {
            handleResult('warning', result);
        } else if (result.error_code === 'RATE_LIMIT_EXCEEDED' && result.student) {
            handleResult('warning', { message: result.message, student: result.student, voucher: null });
        } else {
            showAlert(result.message || 'Scan failed.', 'error');
        }
    } catch (error) {
        showAlert('Network error: ' + error.message, 'error');
    } finally {
        scanBtn.disabled = false;
        scanBtn.innerHTML = '<i class="fas fa-search"></i> Scan for Voucher';
    }
}

function handleResult(type, result) {
    scanView.style.display = 'none';
    resultView.style.display = 'block';

    document.getElementById('resultIcon').textContent = type === 'success' ? '✅' : '⚠️';
    document.getElementById('resultMessage').textContent = type === 'success' ? 'Voucher Redeemed!' : (result.message || 'No Voucher');
    document.getElementById('resultMessage').style.color = type === 'success' ? '#22863a' : '#d97706';

    if (result.student) {
        document.getElementById('studentName').textContent = result.student.name || 'Unknown Student';
        document.getElementById('studentId').textContent = result.student.id || '-';
        const img = document.getElementById('studentPhoto');
        if (result.student.picture) {
            img.src = 'uploads/' + result.student.picture;
        } else {
            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Crect fill="%23e2e8f0" width="100" height="100"/%3E%3C/svg%3E';
        }
        img.onerror = function () {
            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Crect fill="%23e2e8f0" width="100" height="100"/%3E%3C/svg%3E';
        };
        document.getElementById('studentCardDisplay').style.display = 'block';
    } else {
        document.getElementById('studentCardDisplay').style.display = 'none';
    }

    if (result.voucher) {
        document.getElementById('voucherCode').textContent = result.voucher.code || '';
        document.getElementById('voucherMinutes').textContent = (result.voucher.minutes || 0) + ' minutes';
        document.getElementById('voucherDept').textContent = result.voucher.department || 'N/A';
        document.getElementById('voucherCardDisplay').style.display = 'block';
    } else {
        document.getElementById('voucherCardDisplay').style.display = 'none';
    }
}

function resetRFIDForm() {
    scanView.style.display = 'block';
    resultView.style.display = 'none';
    document.getElementById('studentCardDisplay').style.display = 'none';
    document.getElementById('voucherCardDisplay').style.display = 'none';
    alertContainer.innerHTML = '';
    if (rfidInput) {
        rfidInput.value = '';
        rfidInput.focus();
    }
}

function showAlert(message, type) {
    const color = type === 'error' ? '#c53030' : '#3182ce';
    const bg = type === 'error' ? '#fff5f5' : '#eff6ff';
    alertContainer.innerHTML = '<div style="background:' + bg + '; color:' + color + '; padding:1rem; border-radius: var(--radius-sm); border-left:4px solid ' + color + '; font-size:0.875rem;">' + message + '</div>';
}
</script>

<?php include 'includes/footer.php'; ?>
