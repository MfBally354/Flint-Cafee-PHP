<?php
require_once 'config.php';

// Simple admin auth
$adminPass = 'flintadmin2024';
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['pass'] ?? '') === $adminPass) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        showLogin();
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    if ($_POST['status'] === 'completed') {
        $stmt2 = $db->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
        $stmt2->execute([$_POST['order_id']]);
    }
    header('Location: admin.php');
    exit;
}

$db = getDB();
$filterStatus = $_GET['status'] ?? 'all';

$whereClause = $filterStatus !== 'all' ? "WHERE o.status = '$filterStatus'" : "";
$orders = $db->query("
    SELECT o.*, t.table_name
    FROM orders o
    JOIN tables_cafe t ON o.table_id = t.id
    $whereClause
    ORDER BY o.created_at DESC
    LIMIT 100
")->fetchAll();

$stats = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as revenue
    FROM orders WHERE DATE(created_at) = CURDATE()
")->fetch();

function showLogin(): void { ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Flint Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #3C2A21; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'DM Sans', sans-serif; }
        .login-box { background: #FEFAE0; border-radius: 16px; padding: 48px 40px; width: 360px; text-align: center; }
        .login-icon { font-size: 48px; margin-bottom: 8px; }
        h1 { font-family: 'Playfair Display', serif; color: #3C2A21; font-size: 1.8rem; margin-bottom: 4px; }
        p { color: #828E82; font-size: 0.9rem; margin-bottom: 32px; }
        input { width: 100%; padding: 14px 16px; border: 2px solid #D4A373; border-radius: 8px; font-size: 1rem; font-family: inherit; background: #fff; margin-bottom: 16px; color: #3C2A21; }
        button { width: 100%; padding: 14px; background: #3C2A21; color: #FEFAE0; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        button:hover { background: #D4A373; color: #3C2A21; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-icon">⚡</div>
        <h1>Flint Cafe</h1>
        <p>Admin Dashboard</p>
        <form method="POST">
            <input type="password" name="pass" placeholder="Password admin..." required autofocus>
            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>
<?php }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Flint Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<nav class="admin-nav">
    <div class="nav-brand">⚡ <span>Flint Cafe</span> Admin</div>
    <div class="nav-links">
        <a href="qr-generator.php">🔲 QR Generator</a>
        <a href="index.php?meja=1" target="_blank">🌐 Lihat Menu</a>
        <a href="?logout=1" class="logout-btn">Logout</a>
    </div>
</nav>

<main class="admin-main">
    <!-- Stats -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-num"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Order Hari Ini</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-icon">⏳</div>
            <div class="stat-num"><?= $stats['pending'] ?></div>
            <div class="stat-label">Menunggu</div>
        </div>
        <div class="stat-card preparing">
            <div class="stat-icon">👨‍🍳</div>
            <div class="stat-num"><?= $stats['preparing'] ?></div>
            <div class="stat-label">Sedang Dibuat</div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-num"><?= formatRupiah((float)($stats['revenue'] ?? 0)) ?></div>
            <div class="stat-label">Revenue Hari Ini</div>
        </div>
    </section>

    <!-- Filter -->
    <div class="filter-bar">
        <span class="filter-label">Filter Status:</span>
        <?php foreach (['all' => 'Semua', 'pending' => '⏳ Pending', 'confirmed' => '✅ Konfirmasi', 'preparing' => '👨‍🍳 Diproses', 'ready' => '🔔 Siap', 'completed' => '✔️ Selesai'] as $val => $label): ?>
        <a href="?status=<?= $val ?>" class="filter-pill <?= $filterStatus === $val ? 'active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Orders Table -->
    <section class="orders-section">
        <h2 class="section-title">Daftar Pesanan</h2>
        <?php if (empty($orders)): ?>
        <div class="empty-orders">📭 Belum ada pesanan</div>
        <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
            <?php
                $stmt = $db->prepare("SELECT oi.quantity, m.name FROM order_items oi JOIN menu_items m ON oi.menu_item_id = m.id WHERE oi.order_id = ?");
                $stmt->execute([$order['id']]);
                $items = $stmt->fetchAll();
                $statusColors = [
                    'pending'   => '#e67e22',
                    'confirmed' => '#2980b9',
                    'preparing' => '#8e44ad',
                    'ready'     => '#27ae60',
                    'completed' => '#828E82',
                    'cancelled' => '#e74c3c',
                ];
                $statusColor = $statusColors[$order['status']] ?? '#3C2A21';
            ?>
            <div class="order-card">
                <div class="order-card-header">
                    <div class="order-meta">
                        <span class="order-code"><?= $order['order_code'] ?></span>
                        <span class="order-table">📍 <?= htmlspecialchars($order['table_name']) ?></span>
                        <span class="order-name">👤 <?= htmlspecialchars($order['customer_name']) ?></span>
                        <span class="order-time">🕐 <?= date('H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                    <span class="status-badge" style="background: <?= $statusColor ?>20; color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>

                <div class="order-items-list">
                    <?php foreach ($items as $item): ?>
                    <span class="order-item-tag"><?= $item['quantity'] ?>x <?= htmlspecialchars($item['name']) ?></span>
                    <?php endforeach; ?>
                </div>

                <?php if ($order['notes']): ?>
                <div class="order-notes">📝 <?= htmlspecialchars($order['notes']) ?></div>
                <?php endif; ?>

                <div class="order-card-footer">
                    <div class="order-total-wrap">
                        <span class="order-payment"><?= strtoupper($order['payment_method']) ?></span>
                        <span class="order-total"><?= formatRupiah($order['total']) ?></span>
                        <?php if ($order['payment_status'] === 'paid'): ?>
                        <span class="paid-badge">✅ Lunas</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                    <form method="POST" class="status-form">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="update_status" value="1">
                        <select name="status" class="status-select">
                            <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>✅ Konfirmasi</option>
                            <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>👨‍🍳 Sedang Dibuat</option>
                            <option value="ready" <?= $order['status'] === 'ready' ? 'selected' : '' ?>>🔔 Siap Disajikan</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>✔️ Selesai</option>
                            <option value="cancelled">❌ Batalkan</option>
                        </select>
                        <button type="submit" class="update-btn">Update</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<script>
// Auto-refresh every 30 seconds
setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>
