// public_html/modules/Catalog/services/catalogService.js
export const CatalogService = {
    async getProducts(categoryId = '', search = '') {
        try {
            let url = `${CONFIG.API_URL}/products.php?action=list`;
            if (categoryId) url += `&category=${categoryId}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            
            const response = await fetch(url);
            const data = await response.json();
            return data;
        } catch (error) {
            return { success: false, data: [] };
        }
    },

    async getCategories() {
        try {
            const response = await fetch(`${CONFIG.API_URL}/products.php?action=categories`);
            const data = await response.json();
            return data;
        } catch (error) {
            return { success: false, data: [] };
        }
    }
};
