// Initialize Telegram WebApp
let tg = window.Telegram?.WebApp || null;
if (tg) {
    tg.ready();
    tg.expand();
}

// API Base URL
const API_URL = 'api.php';

// State
let currentTab = 'dashboard';
let currentOrderFilter = 'all';
let currentTypeFilter = 'all';
let allOrders = [];
let allPendingOrders = [];
let selectedOrder = null;

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    setupNavigation();
    setupSearchFilters();
    setupModal();
    updateDateTime();
    loadDashboard();
    
    setInterval(updateDateTime, 1000);
    setInterval(loadDashboard, 30000);
}

// ==================== NAVIGATION ====================
function setupNavigation() {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            triggerVibration(30);
            switchTab(item.dataset.tab);
        });
    });
}

function triggerVibration(duration = 30) {
    if (navigator.vibrate) navigator.vibrate(duration);
}

function switchTab(tab) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

    document.getElementById(tab)?.classList.add('active');
    document.querySelector(`[data-tab="${tab}"]`)?.classList.add('active');
    currentTab = tab;

    switch (tab) {
        case 'orders': loadOrders(); break;
        case 'pending': loadPendingOrders(); break;
    }
}

// ==================== SEARCH & FILTER ====================
function setupSearchFilters() {
    // Order search
    const orderSearchEl = document.getElementById('orderSearch');
    if (orderSearchEl) {
        orderSearchEl.addEventListener('input', (e) => renderOrders(e.target.value));
        orderSearchEl.addEventListener('keypress', e => { if (e.key === 'Enter') e.preventDefault(); });
    }

    // Status filter buttons
    document.querySelectorAll('[data-status]').forEach(btn => {
        btn.addEventListener('click', () => {
            triggerVibration(30);
            document.querySelectorAll('[data-status]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentOrderFilter = btn.dataset.status;
            renderOrders();
        });
    });

    // Type filter buttons
    document.querySelectorAll('[data-turi]').forEach(btn => {
        btn.addEventListener('click', () => {
            triggerVibration(30);
            document.querySelectorAll('[data-turi]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTypeFilter = btn.dataset.turi;
            renderOrders();
        });
    });

    // Pending search
    const pendingSearchEl = document.getElementById('pendingSearch');
    if (pendingSearchEl) {
        pendingSearchEl.addEventListener('input', (e) => filterPendingOrders(e.target.value));
    }
}

// ==================== DATE & TIME ====================
function updateDateTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const dateStr = now.toLocaleDateString('uz-UZ', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });

    const timeEl = document.getElementById('headerTime');
    const dateEl = document.getElementById('headerDate');
    if (timeEl) timeEl.textContent = `${hours}:${minutes}:${seconds}`;
    if (dateEl) dateEl.textContent = dateStr;
}

// ==================== DASHBOARD ====================
function loadDashboard() {
    fetch(`${API_URL}?action=get_orders`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                allOrders = data.data;
                updateStats();
            }
        })
        .catch(err => console.error('Dashboard load error:', err));
}

function updateStats() {
    const paidOrders = allOrders.filter(o => o.status === 'paid');
    const pendingCount = allOrders.filter(o => o.status === 'pending').length;

    // Per-type revenue (paid only)
    const types = ['stars', 'premium', 'gift', 'ton'];
    const typeData = {};
    types.forEach(t => {
        const orders = paidOrders.filter(o => o.turi === t);
        typeData[t] = {
            revenue: orders.reduce((sum, o) => sum + (parseInt(o.amount) || 0), 0),
            count: orders.length
        };
    });

    const totalRevenue = paidOrders.reduce((sum, o) => sum + (parseInt(o.amount) || 0), 0);

    // Stars
    document.getElementById('starsRevenue').textContent = typeData.stars.revenue.toLocaleString('uz-UZ') + " so'm";
    document.getElementById('starsCount').textContent = typeData.stars.count + ' ta buyurtma';

    // Premium
    document.getElementById('premiumRevenue').textContent = typeData.premium.revenue.toLocaleString('uz-UZ') + " so'm";
    document.getElementById('premiumCount').textContent = typeData.premium.count + ' ta buyurtma';

    // Gift
    document.getElementById('giftRevenue').textContent = typeData.gift.revenue.toLocaleString('uz-UZ') + " so'm";
    document.getElementById('giftCount').textContent = typeData.gift.count + ' ta buyurtma';

    // TON
    document.getElementById('tonRevenue').textContent = typeData.ton.revenue.toLocaleString('uz-UZ') + " so'm";
    document.getElementById('tonCount').textContent = typeData.ton.count + ' ta buyurtma';

    // General
    document.getElementById('totalRevenue').textContent = totalRevenue.toLocaleString('uz-UZ') + " so'm";
    document.getElementById('totalPaidCount').textContent = paidOrders.length + " to'langan";
    document.getElementById('totalOrders').textContent = allOrders.length.toLocaleString('uz-UZ');
    document.getElementById('pendingOrders').textContent = pendingCount + ' kutilayotgan';
}

// ==================== ORDER TYPE HELPERS ====================
function getTypeInfo(turi) {
    const map = {
        stars:   { emoji: '⭐', label: 'Telegram Stars',   cls: 'badge-stars' },
        premium: { emoji: '💎', label: 'Telegram Premium', cls: 'badge-premium' },
        gift:    { emoji: '🎁', label: 'Telegram Gift',    cls: 'badge-gift' },
        ton:     { emoji: '💠', label: 'TON',              cls: 'badge-ton' }
    };
    return map[turi] || { emoji: '📦', label: turi || 'Noma\'lum', cls: 'badge-default' };
}

function getStatusText(status) {
    return { paid: "To'langan", pending: 'Kutilayotgan', cancelled: 'Bekor qilindi' }[status] || status;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    const pad = n => String(n).padStart(2, '0');
    return `${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function buildOrderDetail(order) {
    const type = getTypeInfo(order.turi);
    let detailLine = '';
    const userDisplay = order.username ? `@${order.username}` : order.user_id;

    if (order.turi === 'stars' && order.quantity) {
        detailLine = `<span class="detail-chip">⭐ ${order.quantity} Stars</span> → <span class="detail-chip user-chip">${userDisplay}</span>`;
    } else if (order.turi === 'premium' && order.mountity) {
        detailLine = `<span class="detail-chip">💎 ${order.mountity} oylik Premium</span> → <span class="detail-chip user-chip">${userDisplay}</span>`;
    } else if (order.turi === 'gift' && order.gift) {
        detailLine = `<span class="detail-chip">${order.gift} Gift</span> → <span class="detail-chip user-chip">${userDisplay}</span>`;
    } else if (order.turi === 'ton' && order.quantity_ton) {
        const wallet = order.hamyon_ton ? order.hamyon_ton.substring(0, 10) + '…' : '—';
        detailLine = `<span class="detail-chip">💠 ${order.quantity_ton} TON</span> → <span class="detail-chip wallet-chip" title="${order.hamyon_ton}">${wallet}</span>`;
    }

    return detailLine;
}

// ==================== ORDERS ====================
function loadOrders() {
    const container = document.getElementById('ordersList');
    if (container) container.innerHTML = '<div class="empty-state" style="opacity:.6"><i class="fas fa-spinner fa-spin"></i><h3>Yuklanmoqda...</h3></div>';

    fetch(`${API_URL}?action=get_orders`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                allOrders = data.data;
                renderOrders();
            } else throw new Error(data.error || 'Failed');
        })
        .catch(err => {
            const c = document.getElementById('ordersList');
            if (c) c.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Xatolik yuz berdi</h3></div>';
        });
}

function renderOrders(search = '') {
    const container = document.getElementById('ordersList');
    let filtered = [...allOrders];

    if (currentOrderFilter !== 'all') filtered = filtered.filter(o => o.status === currentOrderFilter);
    if (currentTypeFilter !== 'all') filtered = filtered.filter(o => o.turi === currentTypeFilter);

    if (search) {
        const s = search.toLowerCase();
        filtered = filtered.filter(o =>
            o.order_id?.toLowerCase().includes(s) ||
            o.username?.toLowerCase().includes(s) ||
            o.user_id?.toString().includes(s)
        );
    }

    if (filtered.length === 0) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i><h3>Buyurtma yo'q</h3></div>`;
        return;
    }

    container.innerHTML = filtered.map(order => {
        const type = getTypeInfo(order.turi);
        const detail = buildOrderDetail(order);
        return `
        <div class="order-card" onclick="openOrderModal(${order.id}); triggerVibration(30);">
            <div class="order-card-top">
                <div class="order-card-id">
                    <span class="type-emoji">${type.emoji}</span>
                    <strong>${order.order_id || order.id}</strong>
                    <span class="type-label-small">${type.label}</span>
                </div>
                <span class="status-badge ${order.status}">${getStatusText(order.status)}</span>
            </div>
            ${detail ? `<div class="order-card-detail">${detail}</div>` : ''}
            <div class="order-card-bottom">
                <span class="order-amount">${parseInt(order.amount).toLocaleString('uz-UZ')} so'm</span>
                <span class="order-date"><i class="fas fa-clock"></i> ${formatDate(order.created_at)}</span>
            </div>
        </div>`;
    }).join('');
}

// ==================== PENDING ORDERS ====================
function loadPendingOrders() {
    const container = document.getElementById('pendingList');
    if (container) container.innerHTML = '<div class="empty-state" style="opacity:.6"><i class="fas fa-spinner fa-spin"></i><h3>Yuklanmoqda...</h3></div>';

    fetch(`${API_URL}?action=get_orders&status=pending`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                allPendingOrders = data.data;
                renderPendingOrders();
            } else throw new Error(data.error || 'Failed');
        })
        .catch(err => {
            const c = document.getElementById('pendingList');
            if (c) c.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Xatolik yuz berdi</h3></div>';
        });
}

function renderPendingOrders(search = '') {
    const container = document.getElementById('pendingList');
    let filtered = [...allPendingOrders];

    if (search) {
        const s = search.toLowerCase();
        filtered = filtered.filter(o =>
            o.order_id?.toLowerCase().includes(s) ||
            o.username?.toLowerCase().includes(s) ||
            o.user_id?.toString().includes(s)
        );
    }

    if (filtered.length === 0) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-hourglass-end"></i><h3>Kutilayotgan buyurtma yo'q</h3></div>`;
        return;
    }

    container.innerHTML = filtered.map(order => {
        const type = getTypeInfo(order.turi);
        const detail = buildOrderDetail(order);
        return `
        <div class="order-card pending-card">
            <div class="order-card-top">
                <div class="order-card-id">
                    <span class="type-emoji">${type.emoji}</span>
                    <strong>${order.order_id || order.id}</strong>
                    <span class="type-label-small">${type.label}</span>
                </div>
                <span class="status-badge pending">Kutilayotgan</span>
            </div>
            ${detail ? `<div class="order-card-detail">${detail}</div>` : ''}
            <div class="order-card-bottom">
                <span class="order-amount">${parseInt(order.amount).toLocaleString('uz-UZ')} so'm</span>
                <span class="order-date"><i class="fas fa-clock"></i> ${formatDate(order.created_at)}</span>
            </div>
            <div class="pending-actions">
                <button class="approve-btn" onclick="event.stopPropagation(); approvePendingOrder(${order.id}); triggerVibration(50);">
                    <i class="fas fa-check"></i> Tasdiqlash
                </button>
                <button class="details-btn" onclick="event.stopPropagation(); openOrderModal(${order.id}); triggerVibration(30);">
                    <i class="fas fa-info-circle"></i> Tafsilot
                </button>
            </div>
        </div>`;
    }).join('');
}

function filterPendingOrders(search) {
    renderPendingOrders(search);
}

function approvePendingOrder(orderId) {
    openOrderModal(orderId);
}

// ==================== ORDER MODAL ====================
function setupModal() {
    const modal = document.getElementById('orderModal');
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    const confModal = document.getElementById('confirmationModal');
    if (confModal) confModal.addEventListener('click', e => { if (e.target === confModal) cancelConfirmation(); });
}

function closeModal() {
    triggerVibration(15);
    document.getElementById('orderModal').classList.remove('active');
}

function openOrderModal(orderId) {
    const numId = parseInt(orderId);
    selectedOrder = allPendingOrders.find(o => o.id == numId) || allOrders.find(o => o.id == numId);

    if (!selectedOrder) {
        showError('Order topilmadi! ID: ' + numId);
        return;
    }

    const type = getTypeInfo(selectedOrder.turi);
    const amount = parseInt(selectedOrder.amount).toLocaleString('uz-UZ');
    const isPending = selectedOrder.status === 'pending';

    // Type badge
    document.getElementById('modalTypeBadge').innerHTML = `${type.emoji} ${type.label}`;
    document.getElementById('modalTypeBadge').className = `modal-type-badge ${type.cls}`;

    // Build product description
    let productBlock = '';
    const userDisplay = selectedOrder.username ? `@${selectedOrder.username}` : selectedOrder.user_id;
    
    if (selectedOrder.turi === 'stars' && selectedOrder.quantity) {
        productBlock = `
        <div class="modal-product-block stars-block">
            <div class="mpb-icon">⭐</div>
            <div class="mpb-info">
                <div class="mpb-title">${selectedOrder.quantity} ta Telegram Stars</div>
                <div class="mpb-sub">Foydalanuvchi: ${userDisplay}</div>
            </div>
        </div>`;
    } else if (selectedOrder.turi === 'premium' && selectedOrder.mountity) {
        productBlock = `
        <div class="modal-product-block premium-block">
            <div class="mpb-icon">💎</div>
            <div class="mpb-info">
                <div class="mpb-title">${selectedOrder.mountity} oylik Telegram Premium</div>
                <div class="mpb-sub">Foydalanuvchi: ${userDisplay}</div>
            </div>
        </div>`;
    } else if (selectedOrder.turi === 'gift') {
        productBlock = `
        <div class="modal-product-block gift-block">
            <div class="mpb-icon">${selectedOrder.gift || '🎁'}</div>
            <div class="mpb-info">
                <div class="mpb-title">Telegram Gift</div>
                <div class="mpb-sub">Oluvchi: ${userDisplay}</div>
                ${selectedOrder.gift_id ? `<div class="mpb-meta">Gift ID: ${selectedOrder.gift_id}</div>` : ''}
            </div>
        </div>`;
    } else if (selectedOrder.turi === 'ton') {
        productBlock = `
        <div class="modal-product-block ton-block">
            <div class="mpb-icon">💠</div>
            <div class="mpb-info">
                <div class="mpb-title">${selectedOrder.quantity_ton} TON</div>
                <div class="mpb-sub">Hamyon: <code class="wallet-code">${selectedOrder.hamyon_ton || '—'}</code></div>
            </div>
        </div>`;
    }

    const modalBody = document.getElementById('orderModalBody');
    modalBody.innerHTML = `
        ${productBlock}
        <div class="modal-info-grid">
            <div class="mig-item">
                <span class="mig-label"><i class="fas fa-hashtag"></i> Order ID</span>
                <span class="mig-value" onclick="copyToClipboard('${selectedOrder.order_id || selectedOrder.id}', 'Order ID'); triggerVibration(20);" style="cursor:pointer;position:relative;" title="Nusxalash uchun bosing">
                    ${selectedOrder.order_id || selectedOrder.id}
                    <i class="fas fa-copy" style="margin-left:6px;opacity:0.6;font-size:12px;"></i>
                </span>
            </div>
            <div class="mig-item">
                <span class="mig-label"><i class="fas fa-user"></i> Foydalanuvchi</span>
                <span class="mig-value" onclick="copyToClipboard('${selectedOrder.username || selectedOrder.user_id}', '${selectedOrder.username ? 'Username' : 'User ID'}'); triggerVibration(20);" style="cursor:pointer;position:relative;" title="Nusxalash uchun bosing">
                    ${userDisplay}
                    <i class="fas fa-copy" style="margin-left:6px;opacity:0.6;font-size:12px;"></i>
                </span>
            </div>
            <div class="mig-item">
                <span class="mig-label"><i class="fas fa-coins"></i> Narx</span>
                <span class="mig-value price-value">${amount} so'm</span>
            </div>
            <div class="mig-item">
                <span class="mig-label"><i class="fas fa-tag"></i> Holat</span>
                <span class="mig-value"><span class="status-badge ${selectedOrder.status}">${getStatusText(selectedOrder.status)}</span></span>
            </div>
            <div class="mig-item full-width">
                <span class="mig-label"><i class="fas fa-calendar"></i> Sana va vaqt</span>
                <span class="mig-value">${formatDate(selectedOrder.created_at)}</span>
            </div>
            ${selectedOrder.updated_at && selectedOrder.updated_at !== selectedOrder.created_at ? `
            <div class="mig-item full-width">
                <span class="mig-label"><i class="fas fa-sync"></i> Yangilangan</span>
                <span class="mig-value">${formatDate(selectedOrder.updated_at)}</span>
            </div>` : ''}
        </div>
    `;

    const confirmBtn = document.getElementById('completeOrderBtn');
    confirmBtn.style.display = isPending ? 'flex' : 'none';

    document.getElementById('orderModal').classList.add('active');
}

// ==================== ORDER COMPLETION ====================
function completeOrder() {
    if (!selectedOrder?.id) { showError("Order tanlanmadi!"); return; }
    showConfirmation(`Order <strong>${selectedOrder.order_id}</strong> ni tasdiqlaysizmi?`);
}

function showConfirmation(message) {
    document.getElementById('confirmationMessage').innerHTML = message;
    document.getElementById('confirmationModal').classList.add('active');
}

function cancelConfirmation() {
    triggerVibration(15);
    document.getElementById('confirmationModal').classList.remove('active');
}

function proceedWithCompletion() {
    if (!selectedOrder) { showError('Order tanlanmadi!'); return; }
    const orderToComplete = selectedOrder;

    document.getElementById('confirmationModal').classList.remove('active');

    const btn = document.getElementById('completeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Tasdiqlanmoqda...';

    fetch(`${API_URL}?action=complete_order`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderToComplete.id })
    })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(data => {
        if (data?.success) {
            closeModal();
            showSuccess(`Order #${orderToComplete.order_id} muvaffaqiyatli tasdiqlandi!`);
            setTimeout(() => {
                loadDashboard();
                if (currentTab === 'orders') loadOrders();
                if (currentTab === 'pending') loadPendingOrders();
            }, 800);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Tasdiqlash';
        } else {
            throw new Error(data?.error || "Noma'lum xatolik");
        }
    })
    .catch(err => {
        showError('Xatolik: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Tasdiqlash';
    });
}

// ==================== COPY TO CLIPBOARD ====================
function copyToClipboard(text, label = 'Text') {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess(`${label} nusxalandi!`);
    }).catch(err => {
        console.error('Copy failed:', err);
    });
}

// ==================== SUCCESS / ERROR ====================
function showSuccess(message) {
    const overlay = document.getElementById('successOverlay');
    document.getElementById('successDetails').textContent = message;
    overlay.classList.add('show');
    setTimeout(() => overlay.classList.remove('show'), 3000);
}

function showError(message) {
    console.error('Error:', message);
    let el = document.getElementById('errorNotification');
    if (!el) {
        el = document.createElement('div');
        el.id = 'errorNotification';
        el.style.cssText = `position:fixed;top:20px;right:20px;background:rgba(255,68,68,.95);color:#fff;padding:16px 20px;border-radius:10px;border:1px solid rgba(255,68,68,.5);box-shadow:0 8px 24px rgba(255,68,68,.3);font-size:14px;z-index:10000;max-width:300px;`;
        document.body.appendChild(el);
    }
    el.textContent = message || 'Xatolik yuz berdi';
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}
