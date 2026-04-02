<?php
require_once 'config.php';

// Check admin session
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
$tables = $db->query("SELECT * FROM tables_cafe WHERE is_active = 1 ORDER BY table_number")->fetchAll();

// Detect server URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8091';
$baseUrl = $protocol . '://' . $host;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Generator - Flint Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --espresso: #3C2A21;
            --latte: #D4A373;
            --sage: #828E82;
            --cream: #FEFAE0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f0e8; font-family: 'DM Sans', sans-serif; color: var(--espresso); }
        .top-nav { background: var(--espresso); color: var(--cream); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; }
        .nav-brand { font-family: 'Playfair Display', serif; font-size: 1.3rem; }
        .nav-back { color: var(--latte); text-decoration: none; font-size: 0.9rem; }
        .page-header { text-align: center; padding: 48px 24px 32px; }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: var(--espresso); margin-bottom: 8px; }
        .page-header p { color: var(--sage); font-size: 1rem; }

        .qr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; padding: 0 32px 48px; max-width: 1200px; margin: 0 auto; }

        .qr-card {
            background: var(--cream);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(60,42,33,0.12);
            transition: transform 0.2s;
        }
        .qr-card:hover { transform: translateY(-4px); }

        .qr-card-header {
            background: var(--espresso);
            color: var(--cream);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .qr-table-name { font-family: 'Playfair Display', serif; font-size: 1.2rem; }
        .qr-capacity { font-size: 0.8rem; opacity: 0.7; }

        .qr-body { padding: 24px; display: flex; flex-direction: column; align-items: center; gap: 16px; }

        .qr-code-wrap {
            background: white;
            border-radius: 12px;
            padding: 16px;
            border: 3px solid var(--latte);
        }
        .qr-code-wrap img { display: block; width: 180px; height: 180px; }

        .qr-url {
            font-size: 0.75rem;
            color: var(--sage);
            text-align: center;
            word-break: break-all;
            background: #f0ebe0;
            padding: 8px 12px;
            border-radius: 8px;
            width: 100%;
        }

        .qr-card-footer {
            display: flex;
            gap: 8px;
            padding: 0 24px 24px;
        }
        .btn-print, .btn-open {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-print { background: var(--espresso); color: var(--cream); }
        .btn-print:hover { background: var(--latte); color: var(--espresso); }
        .btn-open { background: transparent; color: var(--espresso); border: 2px solid var(--espresso); }
        .btn-open:hover { background: var(--espresso); color: var(--cream); }

        @media print {
            .top-nav, .page-header, .qr-card-footer, .print-hide { display: none !important; }
            .qr-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; padding: 0; }
            body { background: white; }
            .qr-card { box-shadow: none; border: 1px solid #ddd; break-inside: avoid; }
        }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">⚡ Flint Cafe · QR Generator</div>
    <a href="admin.php" class="nav-back">← Kembali ke Admin</a>
</nav>

<div class="page-header">
    <h1>🔲 QR Code Meja</h1>
    <p>Scan QR ini untuk mengakses menu di setiap meja</p>
    <p style="margin-top: 8px; font-size: 0.85rem; color: #828E82;">Base URL: <strong><?= $baseUrl ?></strong></p>
    <button onclick="window.print()" class="btn-print" style="margin-top: 16px; padding: 12px 24px; border-radius: 8px;">🖨️ Print Semua QR</button>
</div>

<div class="qr-grid">
    <?php foreach ($tables as $table):
        $menuUrl = $baseUrl . '/index.php?meja=' . $table['table_number'];
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($menuUrl) . '&bgcolor=FFFFFF&color=3C2A21&margin=10';
    ?>
    <div class="qr-card">
        <div class="qr-card-header">
            <span class="qr-table-name"><?= htmlspecialchars($table['table_name']) ?></span>
            <span class="qr-capacity">👥 <?= $table['capacity'] ?> kursi</span>
        </div>
        <div class="qr-body">
            <div class="qr-code-wrap">
                <img src="<?= htmlspecialchars($qrApiUrl) ?>" alt="QR Meja <?= $table['table_number'] ?>" loading="lazy">
            </div>
            <div class="qr-url"><?= htmlspecialchars($menuUrl) ?></div>
        </div>
        <div class="qr-card-footer">
            <button class="btn-print" onclick="printSingle(<?= $table['table_number'] ?>)">🖨️ Print</button>
            <button class="btn-open" onclick="window.open('<?= htmlspecialchars($menuUrl) ?>', '_blank')">🌐 Buka</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function printSingle(tableNum) {
    const url = `<?= $baseUrl ?>/index.php?meja=${tableNum}`;
    const win = window.open('', '_blank', 'width=400,height=500');
    win.document.write(`
        <html><head><title>QR Meja ${tableNum}</title>
        <style>body{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background:#FEFAE0;color:#3C2A21;} h2{font-size:2rem;margin-bottom:16px;} p{color:#828E82;font-size:0.8rem;margin-top:12px;word-break:break-all;text-align:center;max-width:220px;}</style>
        </head><body>
        <h2>⚡ Flint Cafe</h2>
        <h3 style="margin-bottom:16px;">Meja ${tableNum}</h3>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(url)}&bgcolor=FEFAE0&color=3C2A21&margin=10" width="220" height="220">
        <p>Scan untuk memesan</p>
        <p>${url}</p>
        <script>window.onload=()=>window.print()<\/script>
        </body></html>
    `);
}
</script>
</body>
</html>
