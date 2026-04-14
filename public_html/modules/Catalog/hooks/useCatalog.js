// public_html/modules/Catalog/hooks/useCatalog.js
import { ProductListHTML } from '../components/ProductList.js';
import { ProductCardHTML } from '../components/ProductCard.js';
import { CatalogService } from '../services/catalogService.js';

export function useCatalog(container) {
    // 1. Mount the HTML
    container.innerHTML = ProductListHTML;

    // 2. Query DOM logic
    const productGrid = document.getElementById('product-grid');
    const categoryFilter = document.getElementById('category-filter');
    const searchInput = document.getElementById('search-input');
    const datalist = document.getElementById('products-datalist');

    if (!productGrid) return;

    async function initCatalog() {
        await loadCategories();
        await loadProducts();

        // Listeners
        categoryFilter.addEventListener('change', loadProducts);
        
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadProducts, 500);
        });
    }

   function updateDatalist(products, datalist) { 
    if (!datalist) return;
    datalist.innerHTML = '';
    products.forEach(product => {
        const option = document.createElement('option');
        option.value = product.name;
        datalist.appendChild(option);
    });
}

    async function loadCategories() {
        const response = await CatalogService.getCategories();
        if (response.success) {
            response.data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.name;
                categoryFilter.appendChild(option);
            });
        }
    }

    async function loadProducts() {
        productGrid.innerHTML = '<div class="loading-state">Cargando productos...</div>';
        
        const response = await CatalogService.getProducts(categoryFilter.value, searchInput.value);
        
        productGrid.innerHTML = '';
        
        if (response.success && response.data.length > 0) {
        updateDatalist(response.data, datalist);

            let cardsHTML = '';
            response.data.forEach(product => {
                cardsHTML += ProductCardHTML(product);
            });
            productGrid.innerHTML = cardsHTML;

            productGrid.querySelectorAll('.btn-add-cart').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    // Prevent navigation
                    e.stopPropagation();
                    const el = e.currentTarget;
                    if (el.hasAttribute('disabled')) return;
                    const product = {
                        id: el.dataset.id,
                        price: parseFloat(el.dataset.price),
                        name: el.dataset.name,
                        image: el.dataset.img,
                        min_quantity: parseInt(el.dataset.minQuantity) || 200,
        quantity: parseInt(el.dataset.minQuantity) || 200
                    };
                    EventBus.emit('CART_ADD', product);
                });
            });

            productGrid.querySelectorAll('.btn-personalize').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const name = e.target.dataset.name;
                    const message = `Hola! Me gustaría consultar por la personalización de: ${name}`;
                    const wpUrl = `https://wa.me/${CONFIG.WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
                    window.open(wpUrl, '_blank');
                });
            });
        } else {
            productGrid.innerHTML = '<div class="loading-state">No se encontraron productos.</div>';
        }
    }

    initCatalog();
}
