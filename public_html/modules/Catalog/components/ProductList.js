// public_html/modules/Catalog/components/ProductList.js
export const ProductListHTML = `
<div class="catalog-module">
    <div class="catalog-header">
        <h1>Nuestros Productos</h1>
        <div class="filters">
            <select id="category-filter" class="filter-select">
                <option value="">Todas las categorías</option>
            </select>
        <input type="text" id="search-input" class="filter-input" 
                   placeholder="Buscar productos..." 
                   list="products-datalist" 
                   autocomplete="off"> <datalist id="products-datalist"></datalist>
        </div>
    </div>

    <div id="product-grid" class="product-grid">
        <div class="loading-state">Cargando productos...</div>
    </div>
</div>
`;
