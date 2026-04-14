<?php
// public_html/checkout.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Impresos Carnelli</title>
    <link rel="stylesheet" href="https://impresoscarnelli.com/tienda/public_html/globals/main.css">
    <link rel="stylesheet" href="https://impresoscarnelli.com/tienda/public_html/styles/checkout.css">
    <link rel="stylesheet" href="https://impresoscarnelli.com/tienda/public_html/modules/Cart/styles/cart.css">

</head>

<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">IMPRESOS CARNELLI</a>
        </div>
    </header>

    <div class="checkout-page">
        <a href="index.php" class="btn-back">← Volver al catálogo</a>
        <h1>Completa tu compra</h1>

        <div class="checkout-summary" id="checkout-summary">
            <h3>Resumen de tu pedido</h3>
            <div id="checkout-items"></div>
            <h4>Total a pagar: <span id="checkout-total">$0</span></h4>
        </div>

        <form id="checkout-page-form">
            <div class="form-group">
                <label for="name">Nombre completo</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Teléfono (WhatsApp)</label>
                <input type="text" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="shipping_agency">Agencia de Envío</label>
                <select id="shipping_agency" name="shipping_agency" required>
                    <option value="">Selecciona una agencia...</option>
                    <option value="Dac">DAC</option>
                    <option value="Mirtrans">Mirtrans</option>
                    <option value="Nuñez">Nuñez</option>
                    <option value="Turil">Turil</option>
                    <option value="Agencia Central">Agencia Central</option>
                    <option value="Otra">Otra (Especificar en notas)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="address">Dirección de Destino</label>
                <input type="text" id="address" name="address" required
                    placeholder="Calle, número, departamento, ciudad">
            </div>

            <div class="form-group">
                <label for="notes">Notas opcionales (Especificaciones, otra agencia, etc.)</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>

            <button type="submit" class="btn-pay">Pagar con Mercado Pago</button>
        </form>
    </div>

    <!-- Required Scripts -->

    <script src="modules/Cart/services/cartService.js" type="module"></script>
    <script type="module">
        import { CartService } from './modules/Cart/services/cartService.js';

        document.addEventListener('DOMContentLoaded', () => {
            const savedCart = localStorage.getItem('carnelli_cart');
            let cart = [];
            if (savedCart) {
                cart = JSON.parse(savedCart);
            }

            if (cart.length === 0) {
                alert("Tu carrito está vacío.");
                window.location.href = 'index.php';
                return;
            }

            const itemsContainer = document.getElementById('checkout-items');
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.quantity;
                const div = document.createElement('div');
                div.textContent = `${item.quantity}x ${item.name} - $${(item.price * item.quantity).toFixed(2)}`;
                itemsContainer.appendChild(div);
            });

            document.getElementById('checkout-total').textContent = '$' + total.toFixed(2);

            const form = document.getElementById('checkout-page-form');
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Procesando...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                const customerData = Object.fromEntries(formData.entries());

                const response = await CartService.processCheckout(customerData, cart);

                if (response.success && response.preference_url) {
                    // Empty cart before jumping to Mercado Pago so if they go back it's empty? Wait, if they abandon MP we ideally restore stock.
                    // Emptying the cart is standard upon starting checkout. But let's only empty it once payment is known?
                    // Actually, if we empty it now, they can't redo it. I'll empty it to prevent double submission.
                    localStorage.removeItem('carnelli_cart');
                    window.location.href = response.preference_url;
                } else {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    alert('Error: ' + response.message);
                }
            });
        });
    </script>
</body>

</html>