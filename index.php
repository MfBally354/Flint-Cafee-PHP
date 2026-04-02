<?php
require_once 'config.php';

$table_number = isset($_GET['meja']) ? (int)$_GET['meja'] : 0;
$db = getDB();

// Validate table
$table = null;
if ($table_number > 0) {
    $stmt = $db->prepare("SELECT * FROM tables_cafe WHERE table_number = ? AND is_active = 1");
    $stmt->execute([$table_number]);
    $table = $stmt->fetch();
}

// Get categories
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

// Get all menu items grouped by category
$menuByCategory = [];
foreach ($categories as $cat) {
    $stmt = $db->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY sort_order");
    $stmt->execute([$cat['id']]);
    $menuByCategory[$cat['id']] = $stmt->fetchAll();
}

// Get featured items
$featured = $db->query("SELECT m.*, c.name as cat_name FROM menu_items m JOIN categories c ON m.category_id = c.id WHERE m.is_featured = 1 AND m.is_available = 1 LIMIT 6")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - <?= CAFE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ── HEADER ── -->
<header class="site-header">
    <div class="header-inner">
        <div class="logo-wrap">
            <div class="logo-icon">⚡</div>
            <div class="logo-text">
                <span class="logo-name"><?= CAFE_NAME ?></span>
                <span class="logo-tagline"><?= CAFE_TAGLINE ?></span>
            </div>
        </div>
        <?php if ($table): ?>
        <div class="table-badge">
            <span class="table-label">Meja</span>
            <span class="table-num"><?= $table['table_number'] ?></span>
        </div>
        <?php endif; ?>
        <button class="cart-btn" onclick="openCart()">
            <span class="cart-icon">🛒</span>
            <span class="cart-count" id="cartCount">0</span>
        </button>
    </div>
</header>

<!-- ── HERO ── -->
<section class="hero">
    <div class="hero-content">
        <?php if (!$table): ?>
        <div class="no-table-banner">
            <span>⚠️</span>
            <span>Scan QR Code di meja kamu untuk mulai memesan!</span>
        </div>
        <?php else: ?>
        <div class="hero-text">
            <p class="hero-welcome">Selamat Datang di</p>
            <h1 class="hero-title">Flint Cafe</h1>
            <p class="hero-sub">Kamu di <strong><?= htmlspecialchars($table['table_name']) ?></strong> · Pilih menu favoritmu 🤎</p>
        </div>
        <?php endif; ?>
    </div>
    <div class="coffee-beans-bg"></div>
</section>

<!-- ── CATEGORY NAV ── -->
<nav class="cat-nav" id="catNav">
    <div class="cat-nav-inner">
        <button class="cat-pill active" onclick="filterCategory('all')">✨ Semua</button>
        <button class="cat-pill" onclick="filterCategory('featured')">⭐ Favorit</button>
        <?php foreach ($categories as $cat): ?>
        <button class="cat-pill" onclick="filterCategory('<?= $cat['slug'] ?>')" data-cat="<?= $cat['slug'] ?>">
            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
        </button>
        <?php endforeach; ?>
    </div>
</nav>

<!-- ── MAIN CONTENT ── -->
<main class="menu-main">

    <!-- Featured -->
    <section class="menu-section" id="section-featured" data-cat="featured">
        <div class="section-header">
            <h2 class="section-title">⭐ Menu Favorit</h2>
            <p class="section-sub">Pilihan terlaris di Flint Cafe</p>
        </div>
        <div class="menu-grid featured-grid">
            <?php foreach ($featured as $item): ?>
            <div class="menu-card featured-card" data-cat="featured">
                <div class="card-image-wrap">
                    <div class="card-image-placeholder">
                        <?= getCategoryEmoji($item['cat_name']) ?>
                    </div>
                    <?php if ($item['is_featured']): ?>
                    <span class="featured-badge">⭐ Favorit</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                    <div class="card-footer">
                        <span class="item-price"><?= formatRupiah($item['price']) ?></span>
                        <?php if ($table): ?>
                        <button class="add-btn" onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">
                            <span>+</span> Tambah
                        </button>
                        <?php else: ?>
                        <span class="no-order-hint">Scan QR dulu</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- All Categories -->
    <?php foreach ($categories as $cat): ?>
    <?php if (!empty($menuByCategory[$cat['id']])): ?>
    <section class="menu-section" id="section-<?= $cat['slug'] ?>" data-cat="<?= $cat['slug'] ?>">
        <div class="section-header">
            <h2 class="section-title"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></h2>
        </div>
        <div class="menu-grid">
            <?php foreach ($menuByCategory[$cat['id']] as $item): ?>
            <div class="menu-card" data-cat="<?= $cat['slug'] ?>">
                <div class="card-image-wrap">
                    <div class="card-image-placeholder">
                        <?= $cat['icon'] ?>
                    </div>
                    <?php if (!$item['is_available']): ?>
                    <div class="unavailable-overlay">Habis</div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                    <div class="card-footer">
                        <span class="item-price"><?= formatRupiah($item['price']) ?></span>
                        <?php if ($table && $item['is_available']): ?>
                        <button class="add-btn" onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">
                            <span>+</span> Tambah
                        </button>
                        <?php elseif (!$item['is_available']): ?>
                        <span class="sold-out-tag">Habis</span>
                        <?php else: ?>
                        <span class="no-order-hint">Scan QR dulu</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <?php endforeach; ?>

</main>

<!-- ── CART SIDEBAR ── -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<aside class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h2>🛒 Pesananmu</h2>
        <?php if ($table): ?>
        <span class="cart-table-label">Meja <?= $table['table_number'] ?></span>
        <?php endif; ?>
        <button class="close-cart" onclick="closeCart()">✕</button>
    </div>

    <div class="cart-body" id="cartBody">
        <div class="cart-empty" id="cartEmpty">
            <span class="empty-icon">☕</span>
            <p>Keranjangmu masih kosong</p>
            <small>Yuk pilih menu yang kamu suka!</small>
        </div>
        <div id="cartItems"></div>
    </div>

    <div class="cart-footer" id="cartFooter" style="display:none">
        <div class="cart-summary">
            <div class="summary-row"><span>Subtotal</span><span id="cartSubtotal">Rp 0</span></div>
            <div class="summary-row"><span>Pajak (11%)</span><span id="cartTax">Rp 0</span></div>
            <div class="summary-row total-row"><span>Total</span><span id="cartTotal">Rp 0</span></div>
        </div>

        <div class="order-form">
            <input type="text" id="customerName" placeholder="Nama kamu (opsional)" class="name-input">
            <select id="paymentMethod" class="payment-select">
                <option value="cash">💵 Bayar Cash</option>
                <option value="qris">📱 QRIS</option>
                <option value="transfer">🏦 Transfer Bank</option>
            </select>
            <textarea id="orderNotes" placeholder="Catatan pesanan (opsional)..." class="notes-input"></textarea>
        </div>

        <button class="order-btn" onclick="submitOrder()">
            🍽️ Pesan Sekarang
        </button>
    </div>
</aside>

<!-- ── ORDER SUCCESS MODAL ── -->
<div class="modal-overlay" id="successModal" style="display:none">
    <div class="modal-box success-modal">
        <div class="modal-icon">✅</div>
        <h2>Pesanan Berhasil!</h2>
        <p>Pesananmu sudah kami terima. Silakan tunggu ya!</p>
        <div class="order-code-display" id="displayOrderCode"></div>
        <div class="order-summary-display" id="displayOrderSummary"></div>
        <button class="modal-close-btn" onclick="closeSuccessModal()">Tutup</button>
    </div>
</div>

<!-- ── TOAST ── -->
<div class="toast" id="toast"></div>

<footer class="site-footer">
    <p>© 2024 <strong>Flint Cafe</strong> · Made with ☕ & ❤️</p>
</footer>

<?php if ($table): ?>
<input type="hidden" id="tableId" value="<?= $table['id'] ?>">
<input type="hidden" id="tableNumber" value="<?= $table['table_number'] ?>">
<?php endif; ?>

<script src="assets/js/app.js"></script>
</body>
</html>

<?php
function getCategoryEmoji(string $catName): string {
    $map = [
        'Kopi' => '☕', 'Non-Kopi' => '🍵',
        'Minuman Segar' => '🥤', 'Makanan Berat' => '🍽️',
        'Cemilan' => '🍰', 'Paket Hemat' => '🎁',
    ];
    return $map[$catName] ?? '🍴';
}
?>
