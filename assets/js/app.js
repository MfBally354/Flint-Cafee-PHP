// ============================================
// FLINT CAFE - App JavaScript
// ============================================

let cart = [];
const tableId     = document.getElementById('tableId')?.value;
const tableNumber = document.getElementById('tableNumber')?.value;

// ── CART MANAGEMENT ──────────────────────────

function addToCart(id, name, price) {
    if (!tableId) {
        showToast('⚠️ Scan QR meja dulu untuk memesan!');
        return;
    }

    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ id, name, price, qty: 1, notes: '' });
    }

    renderCart();
    updateCartCount();
    showToast(`✅ ${name} ditambahkan!`);

    // Quick bounce animation on cart button
    const btn = document.querySelector('.cart-btn');
    btn.classList.add('bounce');
    setTimeout(() => btn.classList.remove('bounce'), 300);
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
    updateCartCount();
}

function changeQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;

    item.qty += delta;
    if (item.qty <= 0) {
        removeFromCart(id);
        return;
    }

    renderCart();
    updateCartCount();
}

function renderCart() {
    const body   = document.getElementById('cartBody');
    const empty  = document.getElementById('cartEmpty');
    const items  = document.getElementById('cartItems');
    const footer = document.getElementById('cartFooter');

    if (cart.length === 0) {
        empty.style.display = 'block';
        items.innerHTML = '';
        footer.style.display = 'none';
        return;
    }

    empty.style.display = 'none';
    footer.style.display = 'block';

    items.innerHTML = cart.map(item => `
        <div class="cart-item" id="cart-item-${item.id}">
            <div class="cart-item-icon">${getMenuEmoji(item.name)}</div>
            <div class="cart-item-info">
                <div class="cart-item-name">${escHtml(item.name)}</div>
                <div class="cart-item-price">${formatRp(item.price)} / item</div>
            </div>
            <div class="qty-controls">
                <button class="qty-btn" onclick="changeQty(${item.id}, -1)">−</button>
                <span class="qty-num">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${item.id}, 1)">+</button>
            </div>
            <div class="cart-item-subtotal">${formatRp(item.price * item.qty)}</div>
        </div>
    `).join('');

    // Update totals
    const subtotal = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
    const tax      = subtotal * 0.11;
    const total    = subtotal + tax;

    document.getElementById('cartSubtotal').textContent = formatRp(subtotal);
    document.getElementById('cartTax').textContent      = formatRp(tax);
    document.getElementById('cartTotal').textContent    = formatRp(total);
}

function updateCartCount() {
    const count = cart.reduce((sum, i) => sum + i.qty, 0);
    const badge = document.getElementById('cartCount');
    badge.textContent = count;
    badge.classList.toggle('visible', count > 0);
}

// ── CART OPEN/CLOSE ──────────────────────────

function openCart() {
    document.getElementById('cartSidebar').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeCart() {
    document.getElementById('cartSidebar').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

// ── SUBMIT ORDER ─────────────────────────────

async function submitOrder() {
    if (!tableId) {
        showToast('⚠️ Tidak ada meja yang dipilih!');
        return;
    }

    if (cart.length === 0) {
        showToast('🛒 Keranjang masih kosong!');
        return;
    }

    const btn  = document.querySelector('.order-btn');
    btn.disabled = true;
    btn.textContent = '⏳ Memproses...';

    const payload = {
        table_id:       tableId,
        customer_name:  document.getElementById('customerName').value.trim(),
        payment_method: document.getElementById('paymentMethod').value,
        notes:          document.getElementById('orderNotes').value.trim(),
        items: cart.map(i => ({ id: i.id, qty: i.qty, notes: '' }))
    };

    try {
        const res  = await fetch('api.php?action=place_order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            closeCart();
            showSuccessModal(data);
            cart = [];
            renderCart();
            updateCartCount();
        } else {
            showToast('❌ ' + (data.error || 'Terjadi kesalahan'));
        }
    } catch (e) {
        showToast('❌ Gagal mengirim pesanan. Coba lagi!');
    } finally {
        btn.disabled = false;
        btn.textContent = '🍽️ Pesan Sekarang';
    }
}

// ── SUCCESS MODAL ────────────────────────────

function showSuccessModal(data) {
    document.getElementById('displayOrderCode').textContent = data.order_code;
    document.getElementById('displayOrderSummary').innerHTML = `
        📍 ${data.table}<br>
        💰 ${formatRp(data.subtotal)} + pajak ${formatRp(data.tax)}<br>
        <strong>Total: ${formatRp(data.total)}</strong>
    `;
    document.getElementById('successModal').style.display = 'flex';
}

function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
}

// ── CATEGORY FILTER ──────────────────────────

function filterCategory(cat) {
    // Update pills
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    const activePill = cat === 'all'
        ? document.querySelector('.cat-pill:first-child')
        : document.querySelector(`[data-cat="${cat}"]`) || document.querySelectorAll('.cat-pill')[1];
    if (activePill) activePill.classList.add('active');

    // Show/hide sections
    document.querySelectorAll('.menu-section').forEach(section => {
        if (cat === 'all') {
            section.classList.remove('hidden');
        } else {
            const sectionCat = section.getAttribute('data-cat');
            section.classList.toggle('hidden', sectionCat !== cat);
        }
    });

    // Scroll to content
    document.querySelector('.menu-main')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── HELPERS ──────────────────────────────────

function formatRp(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function getMenuEmoji(name) {
    const n = name.toLowerCase();
    if (n.includes('kopi') || n.includes('espresso') || n.includes('latte') || n.includes('cappuccino') || n.includes('americano') || n.includes('brew') || n.includes('flat') || n.includes('mocha') || n.includes('pour')) return '☕';
    if (n.includes('matcha') || n.includes('taro') || n.includes('hojicha') || n.includes('chai') || n.includes('vanilla')) return '🍵';
    if (n.includes('cokelat') || n.includes('chocolate')) return '🍫';
    if (n.includes('lemon') || n.includes('soda') || n.includes('squash') || n.includes('blue') || n.includes('grape') || n.includes('mojito') || n.includes('yakult')) return '🥤';
    if (n.includes('jus') || n.includes('alpukat')) return '🥑';
    if (n.includes('nasi')) return '🍚';
    if (n.includes('pasta')) return '🍝';
    if (n.includes('sandwich') || n.includes('croissant')) return '🥐';
    if (n.includes('salad') || n.includes('quinoa')) return '🥗';
    if (n.includes('oat')) return '🥣';
    if (n.includes('brownies')) return '🍫';
    if (n.includes('cheesecake')) return '🎂';
    if (n.includes('banana')) return '🍌';
    if (n.includes('fries') || n.includes('kentang')) return '🍟';
    if (n.includes('donat')) return '🍩';
    if (n.includes('paket')) return '🎁';
    return '🍴';
}

function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2800);
}

// ── INIT ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    updateCartCount();
});

// Close modal on overlay click
document.getElementById('successModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeSuccessModal();
});

// Keyboard: Escape closes cart
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeCart();
});
