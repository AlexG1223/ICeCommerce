// public_html/app.js
import { useCatalog } from './modules/Catalog/hooks/useCatalog.js';
import { useCart } from './modules/Cart/hooks/useCart.js';
import { useProductDetail } from './modules/ProductDetail/hooks/useProductDetail.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Iniciar carrito global siempre
    useCart();

    // 2. Ruteador Simple (Query Params)
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product_id');
    const appRoot = document.getElementById('app-root');

    if (productId) {
        // Cargar vista de detalle de producto
        useProductDetail(appRoot, productId);
    } else {
        // Cargar Catálogo
        useCatalog(appRoot);
    }
});
