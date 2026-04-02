// public_html/modules/Catalog/components/ProductCard.js

export function ProductCardHTML(product) {
    // The formatCurrency function is global in globals/main.js
    const priceStr = formatCurrency(product.price);
    
    return `
    <div class="product-card" data-id="${product.id}">
        <div class="product-image" style="cursor: pointer;" onclick="window.location.href='index.php?product_id=${product.id}'">
            <img src="${product.image}" alt="${product.name}" loading="lazy">
        </div>
        <div class="product-info">
            <span class="product-category">${product.category_name || 'General'}</span>
            <h3 class="product-name" style="cursor: pointer;" onclick="window.location.href='index.php?product_id=${product.id}'">${product.name}</h3>
            <p class="product-description">${product.description}</p>
            <div class="product-bottom">
                <span class="product-price">${priceStr}</span>
                <div class="product-actions">
                    <button class="btn-outline btn-personalize" data-id="${product.id}" data-name="${product.name}">Personalizar</button>
                    <button class="btn-primary btn-add-cart" data-id="${product.id}" data-price="${product.price}" data-name="${product.name}" data-img="${product.image}">Añadir</button>
                </div>
            </div>
        </div>
    </div>
    `;
}
