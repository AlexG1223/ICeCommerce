// public_html/globals/main.js
const CONFIG = {
    WHATSAPP_NUMBER: '59899123456', // Reemplazar con el número real
    API_URL: '/eCommerce/public_html/api'
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
