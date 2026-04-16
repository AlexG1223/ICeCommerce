const getBaseUrl = () => {
    // Obtenemos la ruta base quitando el nombre del archivo si es necesario
    const path = window.location.pathname;
    const directory = path.substring(0, path.lastIndexOf('/'));
    
    // Si estamos en la raíz o en una página directa, la API suele estar en ./api
    return './api'; 
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
