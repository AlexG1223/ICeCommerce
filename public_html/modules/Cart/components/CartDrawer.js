// public_html/modules/Cart/components/CartDrawer.js

export const CartDrawerHTML = `
<!-- Drawer -->
<div id="cart-overlay" class="cart-overlay"></div>
<div id="cart-drawer" class="cart-drawer">
    <div class="cart-header">
        <h2>Tu Carrito</h2>
        <button id="cart-close" class="btn-close">&times;</button>
    </div>
    
    <div id="cart-items" class="cart-items">
        <div class="cart-empty">Tu carrito está vacío</div>
    </div>
    
    <div class="cart-footer">
        <div class="cart-total">
            <span>Total:</span>
            <span id="cart-total-amount">$0</span>
        </div>
        <button id="btn-checkout" class="btn-primary" style="width: 100%;" disabled>Ir a Pagar</button>
    </div>
</div>

<!-- Modal -->
<div id="checkout-modal" class="modal">
    <div class="modal-content">
        <h2>Finalizar Compra</h2>
        <form id="checkout-form">
            <div class="form-group">
                <label>Nombre y Apellido *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Teléfono *</label>
                <input type="tel" name="phone" required>
            </div>
            <div class="form-group">
                <label>Correo Electrónico *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Dirección de Envío</label>
                <input type="text" name="address">
            </div>
            <div class="form-group">
                <label>Notas adicionales</label>
                <textarea name="notes" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Método de Pago *</label>
                <select name="payment_method" required>
                    <option value="manual">Transferencia / Depósito</option>
                    <option value="mercadopago">Mercado Pago (Tarjeta/Redes)</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-outline" id="btn-cancel-checkout">Cancelar</button>
                <button type="submit" class="btn-primary">Confirmar Pedido</button>
            </div>
        </form>
    </div>
</div>
`;

export function CartItemHTML(item) {
    const minQty = item.min_quantity || 1;
    const currentQty = item.quantity || minQty; // Aseguramos que use la cantidad actual

    return `
    <div class="cart-item">
        <img src="${item.image}" alt="${item.name}" class="cart-item-img">
        <div class="cart-item-info">
            <div class="cart-item-title">${item.name}</div>
            <div style="font-weight:700; color:var(--brand-red);">${formatCurrency(item.price)}</div>
            <div class="cart-item-controls">
                <div class="quantity-ctrl">
                    <button data-id="${item.id}" data-action="minus" class="btn-qty">-</button>
                    <span id="qty-label-${item.id}" class="qty-label" style="padding: 0 10px; font-weight: bold;">${currentQty}</span>
                    <button data-id="${item.id}" data-action="plus" class="btn-qty">+</button>
                </div>
                <button class="cart-item-remove" data-id="${item.id}">Eliminar</button>
            </div>
        </div>
    </div>
    `;
}
