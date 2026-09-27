/**
 * SoraPay User Web App
 * Frontend Logic
 */

// ===================== INITIALIZATION =====================
const tg = window.Telegram?.WebApp;
let currentUser = null;
let products = {};
let cart = [];
let selectedProduct = null;

// Initialize Telegram Web App
if (tg) {
    tg.ready();
    tg.expand();
    tg.enableClosingConfirmation();
    
    currentUser = {
        id: tg.initDataUnsafe?.user?.id || 123456,
        username: tg.initDataUnsafe?.user?.username || 'user',
        firstName: tg.initDataUnsafe?.user?.first_name || 'User'
    };
} else {
    // Fallback for non-Telegram environments
    currentUser = {
        id: 123456,
        username: 'user',
        firstName: 'User'
    };
}

console.log("[v0] Current User:", currentUser);

// ===================== DOM ELEMENTS =====================
const sections = document.querySelectorAll('.section');
const navItems = document.querySelectorAll('.nav-item');
const cartBtn = document.getElementById('cartBtn');
const profileBtn = document.getElementById('profileBtn');
const cartBadge = document.getElementById('cartBadge');
const userGreeting = document.getElementById('userGreeting');
const userBalance = document.getElementById('userBalance');

// Modals
const productModal = document.getElementById('productModal');
const checkoutModal = document.getElementById('checkoutModal');
const tonWalletModal = document.getElementById('tonWalletModal');
const successModal = document.getElementById('successModal');
const toast = document.getElementById('toast');

// Cart
const cartItems = document.getElementById('cartItems');
const cartTotal = document.getElementById('cartTotal');
const checkoutBtn = document.getElementById('checkoutBtn');
const checkoutItems = document.getElementById('checkoutItems');
const checkoutTotal = document.getElementById('checkoutTotal');
const payBtn = document.getElementById('payBtn');

// Product Containers
const starsContainer = document.getElementById('starsContainer');
const premiumContainer = document.getElementById('premiumContainer');
const tonContainer = document.getElementById('tonContainer');
const giftContainer = document.getElementById('giftContainer');

// ===================== LOAD PRODUCTS =====================
async function loadProducts() {
    try {
        console.log("[v0] Loading products from API...");
        
        const response = await fetch('../api/products.php');
        if (!response.ok) throw new Error('Failed to load products');
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error);
        
        products = data.data;
        renderProducts();
        
    } catch (error) {
        console.error("[v0] Error loading products:", error);
        showToast('Mahsulotlarni yuklashda xatolik yuz berdi', 'error');
    }
}

// ===================== RENDER PRODUCTS =====================
function renderProducts() {
    // Clear containers
    starsContainer.innerHTML = '';
    premiumContainer.innerHTML = '';
    tonContainer.innerHTML = '';
    giftContainer.innerHTML = '';

    if (products.stars) {
        products.stars.forEach(product => {
            starsContainer.appendChild(createProductCard(product, 'stars'));
        });
    }

    if (products.premium) {
        products.premium.forEach(product => {
            premiumContainer.appendChild(createProductCard(product, 'premium'));
        });
    }

    if (products.ton) {
        products.ton.forEach(product => {
            tonContainer.appendChild(createProductCard(product, 'ton'));
        });
    }

    if (products.gift) {
        products.gift.forEach(product => {
            giftContainer.appendChild(createProductCard(product, 'gift'));
        });
    }
}

function createProductCard(product, type) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    let icon = '⭐';
    if (type === 'premium') icon = '👑';
    if (type === 'ton') icon = '💎';
    if (type === 'gift') icon = '🎁';

    let displayText = '';
    if (type === 'stars') {
        displayText = `${product.quantity} ⭐`;
    } else if (type === 'premium') {
        displayText = `${product.months} Oy`;
    } else if (type === 'ton') {
        displayText = `${product.amount} TON`;
    } else if (type === 'gift') {
        displayText = product.name || 'Gift';
    }

    card.innerHTML = `
        <div class="product-icon">${icon}</div>
        <div class="product-name">${product.name}</div>
        <div class="product-quantity">${displayText}</div>
        <div class="product-price">${product.price.toLocaleString('uz-UZ')} so'm</div>
    `;

    card.onclick = () => {
        selectedProduct = { ...product, type };
        showProductModal(product, type);
    };

    return card;
}

// ===================== MODALS =====================
function showProductModal(product, type) {
    const modalIcon = document.getElementById('modalProductIcon');
    const modalName = document.getElementById('modalProductName');
    const modalDesc = document.getElementById('modalProductDescription');
    const modalFields = document.getElementById('modalProductFields');
    const modalPrice = document.getElementById('modalProductPrice');
    const addToCartBtn = document.getElementById('addToCartBtn');

    let icon = '⭐';
    let description = '';

    if (type === 'stars') {
        icon = '⭐';
        description = `${product.quantity} ta Telegram Stars sotib oling`;
    } else if (type === 'premium') {
        icon = '👑';
        description = `${product.months} oy Telegram Premium`;
    } else if (type === 'ton') {
        icon = '💎';
        description = `${product.amount} ta TON coin yuborish`;
        modalFields.innerHTML = `
            <div class="form-group">
                <label>TON Hamyon Manzili *</label>
                <input type="text" id="fieldTonWallet" placeholder="Hamyon manzili" class="form-input">
                <small>To'rt harfdan boshlanuvchi manzilni kiriting</small>
            </div>
        `;
    } else if (type === 'gift') {
        icon = '🎁';
        description = `${product.name} Gift Card`;
    }

    modalIcon.textContent = icon;
    modalName.textContent = product.name;
    modalDesc.textContent = description;
    modalPrice.textContent = product.price.toLocaleString('uz-UZ') + ' so\'m';

    addToCartBtn.onclick = () => {
        if (type === 'ton') {
            const wallet = document.getElementById('fieldTonWallet')?.value;
            if (!wallet || wallet.length < 4) {
                showToast('To\'g\'ri hamyon manzilini kiriting', 'error');
                return;
            }
            selectedProduct.tonWallet = wallet;
        }
        addToCart(selectedProduct);
        closeModal('productModal');
        showToast('Savatga qo\'shildi', 'success');
    };

    openModal('productModal');
}

function showCheckoutModal() {
    const checkoutItemsDiv = document.getElementById('checkoutItems');
    checkoutItemsDiv.innerHTML = '';

    let total = 0;
    cart.forEach((item, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'checkout-item';
        
        let info = item.name;
        if (item.type === 'ton') {
            info += ` (${item.tonWallet})`;
        }

        itemDiv.innerHTML = `
            <div>
                <div style="font-weight: 600; margin-bottom: 4px;">${info}</div>
                <div style="font-size: 12px; color: var(--muted-text);">${item.type}</div>
            </div>
            <div style="font-weight: 700; color: var(--secondary-color);">${item.price.toLocaleString('uz-UZ')} so'm</div>
        `;
        
        checkoutItemsDiv.appendChild(itemDiv);
        total += item.price;
    });

    checkoutTotal.textContent = total.toLocaleString('uz-UZ') + ' so\'m';
    openModal('checkoutModal');
}

// ===================== CART FUNCTIONS =====================
function addToCart(product) {
    cart.push(product);
    updateCartUI();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function updateCartUI() {
    cartBadge.textContent = cart.length;
    cartItems.innerHTML = '';

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <p>Savatcha bo'sh</p>
            </div>
        `;
        cartTotal.textContent = '0 so\'m';
        return;
    }

    let total = 0;
    cart.forEach((item, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'cart-item';
        
        let details = item.type;
        if (item.type === 'stars') details += ` - ${item.quantity} ta`;
        if (item.type === 'premium') details += ` - ${item.months} oy`;
        if (item.type === 'ton') details += ` - ${item.amount} ta`;
        if (item.type === 'gift') details += ` - ${item.name}`;

        itemDiv.innerHTML = `
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-details">${details}</div>
            </div>
            <div class="cart-item-price">${item.price.toLocaleString('uz-UZ')} so'm</div>
            <button class="cart-item-remove" onclick="removeFromCart(${index})">O'chirish</button>
        `;
        
        cartItems.appendChild(itemDiv);
        total += item.price;
    });

    cartTotal.textContent = total.toLocaleString('uz-UZ') + ' so\'m';
}

// ===================== CHECKOUT PROCESS =====================
async function processCheckout() {
    if (cart.length === 0) {
        showToast('Savatcha bo\'sh', 'error');
        return;
    }

    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    
    try {
        console.log("[v0] Processing checkout with method:", paymentMethod);
        
        const response = await fetch('../api/create-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: currentUser.id,
                username: currentUser.username,
                items: cart,
                payment_method: paymentMethod
            })
        });

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error);
        }

        console.log("[v0] Order created:", data.order_id);
        
        // Show success modal
        closeModal('checkoutModal');
        document.getElementById('successMessage').textContent = 
            `Buyurtma raqami: ${data.order_id}\n\nAdministrator tez orada javob beradi.`;
        openModal('successModal');

        // Clear cart
        cart = [];
        updateCartUI();

    } catch (error) {
        console.error("[v0] Checkout error:", error);
        showToast('Buyurtma yaratishda xatolik: ' + error.message, 'error');
    }
}

// ===================== MODAL FUNCTIONS =====================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('active');
    if (tg) tg.HapticFeedback?.impactOccurred('light');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('active');
    if (tg) tg.HapticFeedback?.impactOccurred('light');
}

// ===================== SECTION NAVIGATION =====================
function switchSection(sectionId) {
    sections.forEach(section => section.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');

    navItems.forEach(item => {
        item.classList.toggle('active', item.dataset.section === sectionId);
    });

    // Load section-specific data
    if (sectionId === 'profileSection') {
        loadProfileData();
    }
}

// ===================== PROFILE FUNCTIONS =====================
async function loadProfileData() {
    try {
        console.log("[v0] Loading profile data...");
        
        // User info
        document.getElementById('profileUsername').textContent = '@' + currentUser.username;
        document.getElementById('profileUserId').textContent = currentUser.id;
        
        // Load referral data
        const refResponse = await fetch(`../api/user-data.php?user_id=${currentUser.id}`);
        const refData = await refResponse.json();

        if (refData.success) {
            const userData = refData.data;
            
            // Update user balance
            document.getElementById('profileBalance').textContent = 
                userData.balance?.toLocaleString('uz-UZ') + ' so\'m';
            document.getElementById('userBalance').textContent = 
                userData.balance?.toLocaleString('uz-UZ') + ' so\'m';
            
            // Referral info
            document.getElementById('referralLink').value = 
                `https://t.me/${userData.bot_username}?start=${userData.ref_id}`;
            document.getElementById('referralCount').textContent = userData.ref_count || 0;
            document.getElementById('referralBonus').textContent = 
                (userData.ref_bonus || 0).toLocaleString('uz-UZ') + ' so\'m';

            // Order history
            loadOrderHistory();
        }

    } catch (error) {
        console.error("[v0] Error loading profile:", error);
    }
}

async function loadOrderHistory() {
    try {
        const response = await fetch(`../api/user-orders.php?user_id=${currentUser.id}`);
        const data = await response.json();

        const historyDiv = document.getElementById('orderHistory');
        historyDiv.innerHTML = '';

        if (!data.success || !data.orders || data.orders.length === 0) {
            historyDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Hali buyurtma yo'q</p>
                </div>
            `;
            return;
        }

        data.orders.forEach(order => {
            const orderDiv = document.createElement('div');
            orderDiv.className = 'order-item';
            
            const statusText = order.status === 'paid' ? 'To\'langan' : 'Kutilayotgan';
            const statusClass = order.status === 'paid' ? 'paid' : 'pending';
            
            const date = new Date(order.created_at).toLocaleDateString('uz-UZ');

            orderDiv.innerHTML = `
                <div class="order-header">
                    <span class="order-id">#${order.id}</span>
                    <span class="order-status ${statusClass}">${statusText}</span>
                </div>
                <div class="order-details">
                    <div>${order.turi} - ${order.amount?.toLocaleString('uz-UZ')} so'm</div>
                    <div style="font-size: 11px; color: var(--muted-text);">${date}</div>
                </div>
            `;
            
            historyDiv.appendChild(orderDiv);
        });

    } catch (error) {
        console.error("[v0] Error loading orders:", error);
    }
}

// ===================== TOAST NOTIFICATIONS =====================
function showToast(message, type = 'info') {
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ===================== COPY REFERRAL LINK =====================
document.addEventListener('DOMContentLoaded', () => {
    const copyRefBtn = document.getElementById('copyRefBtn');
    if (copyRefBtn) {
        copyRefBtn.addEventListener('click', () => {
            const refLink = document.getElementById('referralLink');
            refLink.select();
            document.execCommand('copy');
            showToast('Nusxa olindi', 'success');
            if (tg) tg.HapticFeedback?.impactOccurred('medium');
        });
    }

    // User greeting
    const hour = new Date().getHours();
    if (hour < 12) {
        userGreeting.textContent = 'Sabah hayir, ' + currentUser.firstName + '!';
    } else if (hour < 18) {
        userGreeting.textContent = 'Tush paiti, ' + currentUser.firstName + '!';
    } else {
        userGreeting.textContent = 'Kechqurun, ' + currentUser.firstName + '!';
    }
});

// ===================== EVENT LISTENERS =====================

// Navigation
navItems.forEach(item => {
    item.addEventListener('click', () => {
        switchSection(item.dataset.section);
    });
});

// Cart button
cartBtn.addEventListener('click', () => {
    switchSection('cartSection');
});

// Profile button
profileBtn.addEventListener('click', () => {
    switchSection('profileSection');
});

// Checkout buttons
checkoutBtn.addEventListener('click', showCheckoutModal);
payBtn.addEventListener('click', processCheckout);

// Modal close buttons
document.getElementById('closeProductModal')?.addEventListener('click', () => closeModal('productModal'));
document.getElementById('closeCheckoutModal')?.addEventListener('click', () => closeModal('checkoutModal'));
document.getElementById('closeTonWalletModal')?.addEventListener('click', () => closeModal('tonWalletModal'));
document.getElementById('successCloseBtn')?.addEventListener('click', () => {
    closeModal('successModal');
    switchSection('productsSection');
});

// Close modals on background click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
});

// Initialize
window.addEventListener('load', () => {
    console.log("[v0] App initialized");
    loadProducts();
    loadProfileData();
});
