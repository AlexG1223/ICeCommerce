// public_html/modules/Cart/hooks/useCart.js
import { CartDrawerHTML, CartItemHTML } from '../components/CartDrawer.js';
import { CartService } from '../services/cartService.js';

export function useCart() {
    const cartRoot = document.getElementById('cart-root');
    if (!cartRoot) return;
    
    // Inject HTML
    cartRoot.innerHTML = CartDrawerHTML;

    // DOM Elements
    const cartToggle = document.getElementById('cart-toggle');
    const cartClose = document.getElementById('cart-close');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartCount = document.getElementById('cart-count');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalAmount = document.getElementById('cart-total-amount');
    const btnCheckout = document.getElementById('btn-checkout');
    
    const checkoutModal = document.getElementById('checkout-modal');
    const btnCancelCheckout = document.getElementById('btn-cancel-checkout');
    const checkoutForm = document.getElementById('checkout-form');

    // State
    let cart = [];

    function initCart() {
        const saved = localStorage.getItem('carnelli_cart');
        if (saved) {
            cart = JSON.parse(saved);
        }
        renderCart();

        // Prevent attaching multiple EventBus listeners by clearing previous ones (if using SPA re-renders, not needed since useCart is global once)
        EventBus.events['CART_ADD'] = [];
        EventBus.on('CART_ADD', (product) => {
            addToCart(product);
            openCart();
        });
    }

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        saveCart();
        renderCart();
    }

    function updateQuantity(id, delta) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                cart = cart.filter(i => i.id !== id);
            }
            saveCart();
            renderCart();
        }
    }

function removeCartItem(id) {
    console.log("ID a eliminar:", id);

    cart = cart.filter(item => item.id !== Number(id));

    localStorage.setItem("cart", JSON.stringify(cart));

    console.log("Carrito actualizado:", cart);
    console.log("LocalStorage:", JSON.parse(localStorage.getItem("cart")));

    renderCart();
}

    function saveCart() {
        localStorage.setItem('carnelli_cart', JSON.stringify(cart));
    }

    // UI Rendering
    function renderCart() {
        let total = 0;
        let count = 0;
        cartItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<div class="cart-empty">Tu carrito está vacío</div>';
            btnCheckout.disabled = true;
        } else {
            btnCheckout.disabled = false;
            let itemsHTML = '';
            cart.forEach(item => {
                total += item.price * item.quantity;
                count += item.quantity;
                itemsHTML += CartItemHTML(item);
            });
            cartItemsContainer.innerHTML = itemsHTML;
        }

        cartTotalAmount.textContent = formatCurrency(total);
        cartCount.textContent = count;

        // Attach listeners to newly rendered items
        cartItemsContainer.querySelectorAll('.quantity-ctrl button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                const action = e.target.dataset.action;
                updateQuantity(id, action === 'plus' ? 1 : -1);
            });
        });

        cartItemsContainer.querySelectorAll('.cart-item-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                removeCartItem(e.target.dataset.id);
            });
        });
    }

    function openCart() {
        cartDrawer.classList.add('open');
        cartOverlay.classList.add('show');
    }
    
    function closeCart() {
        cartDrawer.classList.remove('open');
        cartOverlay.classList.remove('show');
    }

    cartToggle.addEventListener('click', openCart);
    cartClose.addEventListener('click', closeCart);
    cartOverlay.addEventListener('click', closeCart);

    btnCheckout.addEventListener('click', () => {
        closeCart();
        checkoutModal.classList.add('show');
    });

    btnCancelCheckout.addEventListener('click', () => {
        checkoutModal.classList.remove('show');
    });

    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(checkoutForm);
        const customerData = Object.fromEntries(formData.entries());
        
        btnCheckout.disabled = true;
        const submitBtn = checkoutForm.querySelector('button[type="submit"]');
        submitBtn.textContent = 'Procesando...';
        submitBtn.disabled = true;

        const response = await CartService.processCheckout(customerData, cart);
        
        submitBtn.textContent = 'Confirmar Pedido';
        submitBtn.disabled = false;

        if (response.success) {
            cart = [];
            saveCart();
            renderCart();
            checkoutModal.classList.remove('show');
            checkoutForm.reset();
            
            if (customerData.payment_method === 'mercadopago' && response.preference_id) {
                alert(`Pedido #${response.order_id} creado correctamente. Redirigiendo a Mercado Pago...`);
            } else {
                alert(`Pedido #${response.order_id} creado correctamente. Nos contactaremos con usted para el pago manual.`);
            }
        } else {
            alert('Error: ' + response.message);
        }
    });

    initCart();
}
