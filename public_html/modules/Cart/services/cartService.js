// public_html/modules/Cart/services/cartService.js
export const CartService = {
    async processCheckout(customerData, cartItems) {
        try {
            const response = await fetch(`${CONFIG.API_URL}/checkout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    customer: customerData,
                    cart: cartItems
                })
            });
            return await response.json();
        } catch (error) {
            console.error('Error during checkout:', error);
            return { success: false, message: 'Error de red o de servidor.' };
        }
    }
};
