// public_html/modules/Cart/services/cartService.js

export const CartService = {
    async processCheckout(customerData, cartItems) {
        console.log('[CartService] ========== processCheckout INICIO ==========');
        console.log('[CartService] 👤 customerData:', JSON.stringify(customerData, null, 2));
        console.log('[CartService] 🛒 cartItems:', JSON.stringify(cartItems, null, 2));
        console.log('[CartService] 🛒 Cantidad de items:', cartItems.length);
        
        try {
            const requestData = {
                customer: customerData,
                cart: cartItems
            };
            console.log('[CartService] 📤 Request body armado:', JSON.stringify(requestData));
            const apiUrl = (window.CONFIG && window.CONFIG.API_URL) ? window.CONFIG.API_URL : './api';
            console.log(`[CartService] 🌐 Enviando POST a ${apiUrl}/checkout.php...`);
            
            const startTime = performance.now();
            
            const response = await fetch(`${apiUrl}/checkout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });
            
            const elapsed = (performance.now() - startTime).toFixed(0);
            console.log(`[CartService] ⏱️ Respuesta recibida en ${elapsed}ms`);
            console.log('[CartService] 📥 HTTP Status:', response.status, response.statusText);
            console.log('[CartService] 📥 Content-Type:', response.headers.get('content-type'));
            
            const rawText = await response.text();
            console.log('[CartService] 📥 Raw response text:', rawText);
            
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseError) {
                console.error('[CartService] ❌ Error parseando JSON:', parseError.message);
                console.error('[CartService] ❌ Response text que falló:', rawText);
                return { success: false, message: 'Respuesta inválida del servidor.' };
            }
            
            console.log('[CartService] 📦 Datos parseados:', data);
            console.log('[CartService] ✅ success:', data.success);
            if (data.success) {
                console.log('[CartService] ✅ order_id:', data.order_id);
                console.log('[CartService] ✅ preference_id:', data.preference_id);
                console.log('[CartService] ✅ preference_url:', data.preference_url);
            } else {
                console.error('[CartService] ❌ Error message:', data.message);
            }
            console.log('[CartService] ========== processCheckout FIN ==========');
            return data;
        } catch (error) {
            console.error('[CartService] ❌ Excepción en processCheckout:', error);
            console.error('[CartService] ❌ Stack:', error.stack);
            return { success: false, message: 'Error de red o de servidor.' };
        }
    }
};
