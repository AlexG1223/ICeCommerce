// public_html/modules/ProductDetail/hooks/useProductDetail.js
import { ProductViewHTML } from '../components/ProductView.js';

export async function useProductDetail(container, productId) {
    container.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--brand-gray);">Cargando producto...</div>';
    
    try {
        const response = await fetch(`${CONFIG.API_URL}/products.php?action=detail&id=${productId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const product = data.data;
            container.innerHTML = ProductViewHTML(product);

            // Bind thumbnails logic
            const thumbnails = container.querySelectorAll('.detail-thumb');
            const mainImg = document.getElementById('main-product-image');
            
if (mainImg && thumbnails.length > 0) {
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', (e) => {
            const newSrc = e.target.dataset.url;
            if (newSrc) {
                mainImg.src = newSrc;
            }
        });
    });
} else {
}

            // Bind events
            const btnAdd = document.getElementById('btn-detail-add');
            const btnPersonalize = document.getElementById('btn-detail-personalize');

            if (btnAdd) {
                btnAdd.addEventListener('click', () => {
                    if (btnAdd.hasAttribute('disabled')) return;
                    EventBus.emit('CART_ADD', {
                        id: product.id,
                        price: parseFloat(product.price),
                        name: product.name,
                        image: product.image,
                        min_quantity: product.min_quantity,
                        quantity: product.min_quantity
                    });
                });
            }

            if (btnPersonalize) {
                btnPersonalize.addEventListener('click', () => {
                    const message = `Hola! Me gustaría consultar por la personalización de: ${product.name}`;
                    const wpUrl = `https://wa.me/${CONFIG.WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
                    window.open(wpUrl, '_blank');
                });
            }

        } else {
            container.innerHTML = `
                <div style="padding: 40px; text-align: center;">
                    <h2>Producto no encontrado</h2>
                    <br>
                    <a href="index.php" class="btn-primary">Volver al catálogo</a>
                </div>
            `;
        }
    } catch (err) {
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--brand-red);">Ocurrió un error.</div>';
    }
}
