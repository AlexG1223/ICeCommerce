// public_html/modules/Catalog/services/catalogService.js
export const CatalogService = {
    async getProducts(categoryId = '', search = '') {
        try {
            let url = `${CONFIG.API_URL}/products.php?action=list`;
            if (categoryId) url += `&category=${categoryId}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error('Error fetching products:', error);
            return { success: false, data: [] };
        }
    },

    async getCategories() {
        try {
            const response = await fetch(`${CONFIG.API_URL}/products.php?action=categories`);
            return await response.json();
        } catch (error) {
            console.error('Error fetching categories:', error);
            return { success: false, data: [] };
        }
    }
};
