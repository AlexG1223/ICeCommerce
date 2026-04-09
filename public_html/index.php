<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresos Carnelli</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <!-- Globals -->
    <link rel="stylesheet" href="globals/main.css">
    
    <!-- Module Styles -->
    <link rel="stylesheet" href="modules/Catalog/styles/catalog.css">
    <link rel="stylesheet" href="modules/ProductDetail/styles/detail.css">
    <link rel="stylesheet" href="modules/Cart/styles/cart.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">IMPRESOS CARNELLI</a>
            <button class="cart-button" id="cart-toggle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                Carrito
                <span class="cart-count" id="cart-count">0</span>
            </button>
        </div>
    </header>

    <!-- App Root for Main Views (Catalog / Detail) -->
    <main class="container" id="app-root">
        <div style="padding: 40px; text-align: center; color: var(--brand-gray);">Cargando la aplicación...</div>
    </main>

    <!-- Componente inyectado globalmente: Carrito Sidebar y Modal -->
    <div id="cart-root"></div>

    <!-- Global Scripts -->
    <script src="globals/main.js"></script>

    <!-- Main Module Point -->
    <script type="module" src="app.js"></script>
</body>
</html>