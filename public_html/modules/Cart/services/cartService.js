// public_html/modules/Cart/services/cartService.js

export const CartService = {
    async processCheckout(customerData, cartItems) {
   
        try {
            const requestData = {
                customer: customerData,
                cart: cartItems
            };
            console.log("Checkout Data Sent:", requestData);
            
            const response = await fetch(`/eCommerce/public_html/api/checkout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });
            const data = await response.json();
            console.log("Checkout Data Received:", data);
            return data;
        } catch (error) {
            console.error('Error during checkout:', error);
            return { success: false, message: 'Error de red o de servidor.' };
        }
    }
};
