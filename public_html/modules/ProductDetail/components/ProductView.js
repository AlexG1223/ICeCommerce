// public_html/modules/ProductDetail/components/ProductView.js

export function ProductViewHTML(product) {
    const priceStr = formatCurrency(product.price);
    
    let thumbnailsHTML = '';
    if (product.images && product.images.length > 0) {
        thumbnailsHTML = `
            <div class="detail-thumbnails">
                ${product.images.map(img => `<img src="${img.url}" class="detail-thumb" data-url="${img.url}" alt="${product.name}">`).join('')}
            </div>
        `;
    }
    
    return `
    <div>
        <a href="index.php" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Volver al Catálogo
        </a>
        <div class="product-detail-view">
            <div class="detail-image-box">
                <img src="${product.image}" id="main-product-image" alt="${product.name}">
                ${thumbnailsHTML}
            </div>
            
            <div class="detail-info-box">
                <div class="detail-category">${product.category_name || 'General'}</div>
                <h1 class="detail-title">${product.name}</h1>
                <div class="detail-price">${priceStr}</div>
                <p class="detail-description">${product.description}</p>
                
                <div class="detail-actions">
                    <button class="btn-outline" id="btn-detail-personalize">Personalizar por WhatsApp</button>
                    <button class="btn-primary" id="btn-detail-add">Añadir al Carrito</button>
                </div>
            </div>
        </div>
    </div>
    `;
}
