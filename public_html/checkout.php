<?php
// public_html/checkout.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Impresos Carnelli</title>
    <link rel="stylesheet" href="globals/main.css">
    <link rel="stylesheet" href="styles/checkout.css">
    <link rel="stylesheet" href="modules/Cart/styles/cart.css">

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
    <script src="globals/main.js"></script>
    <script src="modules/Cart/services/cartService.js" type="module"></script>
    <script type="module">
        import { CartService } from './modules/Cart/services/cartService.js';

        document.addEventListener('DOMContentLoaded', () => {
            console.log('[CHECKOUT] 🟢 Página de checkout cargada');

            const savedCart = localStorage.getItem('carnelli_cart');
            let cart = [];
            if (savedCart) {
                cart = JSON.parse(savedCart);
            }

            console.log('[CHECKOUT] 🛒 Carrito desde localStorage:', cart);
            console.log('[CHECKOUT] 🛒 Cantidad de items:', cart.length);

            if (cart.length === 0) {
                console.warn('[CHECKOUT] ⚠️ Carrito vacío, redirigiendo a index');
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

            console.log('[CHECKOUT] 💰 Total calculado:', total);
            document.getElementById('checkout-total').textContent = '$' + total.toFixed(2);

            const form = document.getElementById('checkout-page-form');
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                console.log('[CHECKOUT] 📝 Formulario enviado');

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Procesando...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                const customerData = Object.fromEntries(formData.entries());

                console.log('[CHECKOUT] 👤 Datos del cliente:', customerData);
                console.log('[CHECKOUT] 🛒 Items del carrito a enviar:', cart);
                console.log('[CHECKOUT] 🚀 Enviando a CartService.processCheckout...');

                const response = await CartService.processCheckout(customerData, cart);

                console.log('[CHECKOUT] 📦 Respuesta del servidor:', response);

                if (response.success && response.preference_url) {
                    console.log('[CHECKOUT] ✅ Pago OK - preference_url:', response.preference_url);
                    console.log('[CHECKOUT] ✅ order_id:', response.order_id);
                    console.log('[CHECKOUT] ✅ preference_id:', response.preference_id);
                    console.log('[CHECKOUT] 🔄 Limpiando carrito y redirigiendo a Mercado Pago...');
                    localStorage.removeItem('carnelli_cart');
                    window.location.href = response.preference_url;
                } else {
                    console.error('[CHECKOUT] ❌ Error en respuesta:', response.message);
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    alert('Error: ' + response.message);
                }
            });
        });
    </script>
</body>

</html>