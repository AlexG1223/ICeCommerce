// public_html/modules/Cart/services/cartService.js

export const CartService = {
    async processCheckout(customerData, cartItems) {
        
        try {
            const requestData = {
                customer: customerData,
                cart: cartItems
            };
            const apiUrl = (window.CONFIG && window.CONFIG.API_URL) ? window.CONFIG.API_URL : './api';
            
            const startTime = performance.now();
            
            const response = await fetch(`${apiUrl}/checkout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });
            
            const elapsed = (performance.now() - startTime).toFixed(0);
            
            const rawText = await response.text();
            
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseError) {
                return { success: false, message: 'Respuesta inválida del servidor.' };
            }
            
            if (data.success) {
            } else {
            }
            return data;
        } catch (error) {
            return { success: false, message: 'Error de red o de servidor.' };
        }
    }
};
