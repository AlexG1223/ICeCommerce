const getBaseUrl = () => {
    const isLocal = window.location.hostname === 'localhost';
    // Si estás en producción usa la ruta de la tienda, si no, la de localhost
    return isLocal 
        ? '/eCommerce/public_html/api' 
        : '/public/tienda/api'; 
};

const CONFIG = {
    WHATSAPP_NUMBER: '59899123456', 
    API_URL: getBaseUrl(),
    CURRENCY: 'UYU',
    LOCALE: 'es-UY'
};

// Utilities for currency formatting
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-UY', {
        style: 'currency',
        currency: 'UYU',
        minimumFractionDigits: 0
    }).format(amount);
}

// Global Event Emitter simple (Publisher/Subscriber)
const EventBus = {
    events: {},
    on(event, listener) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(listener);
    },
    emit(event, data) {
        if (this.events[event]) {
            this.events[event].forEach(listener => listener(data));
        }
    }
};
