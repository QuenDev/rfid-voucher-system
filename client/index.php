<?php
/**
 * Client Router / Entry Point
 * 
 * This router handles all client-side page requests and provides
 * centralized routing, authentication, and error handling.
 */

// Determine the requested page
$page = isset($_GET['page']) ? basename($_GET['page']) : 'dashboard';

// List of allowed pages
$allowed_pages = [
    'dashboard',
    'rfid',
    'vouchers',
    'students',
    'accounts',
    'reports',
    'add',
    'edit',
    'add_student',
    'edit_student',
    'view_student',
    'add_account',
    'edit_account'
];

// Validate page request
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Construct the page file path
$page_file = __DIR__ . '/' . $page . '.php';

// Check if the page file exists
if (!file_exists($page_file)) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page Not Found</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .container {
                text-align: center;
                background: white;
                padding: 3rem;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            h1 { color: #333; margin: 0; }
            p { color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404 - Page Not Found</h1>
            <p>The page you requested does not exist.</p>
            <p><a href="?page=dashboard" style="color: #667eea; text-decoration: none;">Go to Dashboard</a></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Include the requested page
include $page_file;
?>
