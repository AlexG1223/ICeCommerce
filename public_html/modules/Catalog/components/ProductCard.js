// public_html/modules/Catalog/components/ProductCard.js

export function ProductCardHTML(product) {
    // The formatCurrency function is global in globals/main.js
    const priceStr = formatCurrency(product.price);
    
    const stock = parseInt(product.stock) || 0;
    const minQty = parseInt(product.min_quantity) || 1;
    const isAgotado = stock < minQty;
    
    return `
    <div class="product-card" data-id="${product.id}">
        <div class="product-image" style="cursor: pointer; position: relative;" onclick="window.location.href='index.php?product_id=${product.id}'">
            <img src="${product.image}" alt="${product.name}" loading="lazy">
            ${isAgotado ? '<div style="position: absolute; top: 10px; right: 10px; background: red; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">Agotado</div>' : ''}
        </div>
        <div class="product-info">
            <span class="product-category">${product.category_name || 'General'}</span>
            <h3 class="product-name" style="cursor: pointer;" onclick="window.location.href='index.php?product_id=${product.id}'">${product.name}</h3>
            <p class="product-description">${product.description}</p>
            <div class="product-bottom">
                <span class="product-price">${priceStr}</span>
                <div class="product-actions" style="min-height: 40px; display: flex; align-items: center; justify-content: flex-end;">
                   ${!isAgotado ? `
                   <button 
    class="btn-primary btn-add-cart" 
    data-id="${product.id}" 
    data-price="${product.price}" 
    data-name="${product.name}" 
    data-img="${product.image}"
    data-min-quantity="${product.min_quantity}" 
    data-quantity="${product.min_quantity}"
>Añadir</button>` : ''}
                </div>
            </div>
        </div>
    </div>
    `;
}
