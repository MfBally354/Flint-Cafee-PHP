<?php
// ============================================
// FLINT CAFE - Database Configuration
// ============================================

define('DB_HOST', 'db');
define('DB_NAME', 'flintcafe');
define('DB_USER', 'root');
define('DB_PASS', '#iqbaldebian#');
define('DB_CHARSET', 'utf8mb4');

define('CAFE_NAME', 'Flint Cafe');
define('CAFE_TAGLINE', 'Where Every Sip Tells a Story');
define('TAX_RATE', 0.11); // 11% PPN

// Fungsi untuk menampilkan halaman error yang menarik
function showDatabaseError($technicalMessage = '') {
    // Membersihkan output buffer jika ada
    if (ob_get_level()) ob_clean();
    
    // Set header HTTP response code
    http_response_code(503);
    
    // Tampilkan HTML dengan styling modern
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Error - ' . CAFE_NAME . '</title>
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "Poppins", sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
                animation: fadeIn 0.5s ease-out;
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            
            .error-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                width: 100%;
                overflow: hidden;
                animation: slideUp 0.5s ease-out;
            }
            
            @keyframes slideUp {
                from {
                    transform: translateY(30px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .error-header {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                padding: 30px;
                text-align: center;
            }
            
            .warning-icon {
                font-size: 70px;
                animation: pulse 2s infinite;
                display: inline-block;
            }
            
            .error-header h1 {
                color: white;
                font-size: 24px;
                margin-top: 15px;
                font-weight: 600;
            }
            
            .error-body {
                padding: 30px;
            }
            
            .error-message {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            
            .error-message p {
                color: #856404;
                font-weight: 500;
                margin: 0;
            }
            
            .info-box {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 15px;
                margin: 20px 0;
            }
            
            .info-item {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
                font-size: 14px;
            }
            
            .info-item:last-child {
                margin-bottom: 0;
            }
            
            .info-icon {
                width: 30px;
                color: #6c757d;
                font-size: 18px;
            }
            
            .info-text {
                color: #495057;
                flex: 1;
            }
            
            .info-text strong {
                color: #212529;
            }
            
            .technical-details {
                background: #2d3748;
                border-radius: 10px;
                padding: 15px;
                margin-top: 20px;
                font-family: "Courier New", monospace;
                font-size: 12px;
                color: #a0aec0;
                overflow-x: auto;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .technical-details:hover {
                background: #1a202c;
            }
            
            .technical-details .label {
                color: #fc8181;
                font-weight: bold;
                display: block;
                margin-bottom: 8px;
            }
            
            .technical-details .content {
                color: #e2e8f0;
                word-break: break-word;
            }
            
            .technical-details.collapsed .content {
                display: none;
            }
            
            .action-buttons {
                display: flex;
                gap: 15px;
                margin-top: 25px;
            }
            
            .btn {
                flex: 1;
                padding: 12px 20px;
                border: none;
                border-radius: 8px;
                font-family: "Poppins", sans-serif;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                text-align: center;
                display: inline-block;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }
            
            .btn-secondary {
                background: #e9ecef;
                color: #495057;
            }
            
            .btn-secondary:hover {
                background: #dee2e6;
                transform: translateY(-2px);
            }
            
            .footer {
                background: #f8f9fa;
                padding: 15px;
                text-align: center;
                font-size: 12px;
                color: #6c757d;
            }
            
            @media (max-width: 768px) {
                .error-container {
                    margin: 20px;
                }
                
                .action-buttons {
                    flex-direction: column;
                }
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-header">
                <div class="warning-icon">⚠️</div>
                <h1>Database Error</h1>
            </div>
            <div class="error-body">
                <div class="error-message">
                    <p>🔌 <strong>Diagram tidak terhubung</strong></p>
                    <p style="margin-top: 5px; font-size: 14px;">Sistem tidak dapat terhubung ke database. Silakan periksa koneksi database Anda.</p>
                </div>
                
                <div class="info-box">
                    <div class="info-item">
                        <div class="info-icon">🕐</div>
                        <div class="info-text"><strong>Waktu:</strong> ' . date('d/m/Y H:i:s') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">🏠</div>
                        <div class="info-text"><strong>Host:</strong> ' . DB_HOST . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">🗄️</div>
                        <div class="info-text"><strong>Database:</strong> ' . DB_NAME . '</div>
                    </div>
                </div>';
    
    // Tampilkan technical details hanya jika ada pesan teknis
    if (!empty($technicalMessage)) {
        echo '
                <div class="technical-details" onclick="this.classList.toggle(\'collapsed\')">
                    <div class="label">🔧 Technical Details (Klik untuk toggle)</div>
                    <div class="content">' . htmlspecialchars($technicalMessage) . '</div>
                </div>';
    }
    
    echo '
                <div class="action-buttons">
                    <button onclick="location.reload()" class="btn btn-primary">
                        🔄 Coba Lagi
                    </button>
                    <button onclick="window.location.href=\'/\'" class="btn btn-secondary">
                        🏠 Kembali ke Beranda
                    </button>
                </div>
            </div>
            <div class="footer">
                ' . CAFE_NAME . ' - ' . CAFE_TAGLINE . '
            </div>
        </div>
        
        <script>
            // Auto refresh setiap 30 detik
            setTimeout(function() {
                location.reload();
            }, 30000);
            
            // Menampilkan notifikasi di console
            console.log("%c⚠️ Database Error", "color: #f5576c; font-size: 16px; font-weight: bold;");
            console.log("%cDiagram tidak terhubung - ' . date('Y-m-d H:i:s') . '", "color: #856404;");
        </script>
    </body>
    </html>';
    exit();
}

function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Tampilkan halaman error yang menarik
            showDatabaseError($e->getMessage());
        }
    }
    return $pdo;
}

function generateOrderCode(): string {
    return 'FLT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

session_start();
?>
