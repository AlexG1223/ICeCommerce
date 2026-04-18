<?php
// failure.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='icon' type="image/png" href="/public/tienda/assets/img/iclogo.png">
    <title>Pago Fallido - Impresos Carnelli</title>
    <link rel="stylesheet" href="globals/main.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            background: var(--surface-color);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .icon-failure {
            font-size: 60px;
            color: #f44336;
            margin-bottom: var(--spacing-md);
        }

        h1 {
            color: var(--brand-black);
            margin-bottom: var(--spacing-md);
        }

        p {
            color: var(--brand-dark-gray);
            margin-bottom: var(--spacing-lg);
        }
    </style>
</head>

<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">Impresos Carnelli</a>
            <div class="header-actions">
                <a href="https://www.impresoscarnelli.com/page/" class="nav-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="hide-mobile">Inicio</span>
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="result-container">
            <div class="icon-failure">✗</div>
            <h1>Pago Fallido</h1>
            <p>Ocurrió un problema al procesar tu pago. Por favor, intenta nuevamente más tarde o utiliza otro método de
                pago.</p>
            <a href="index.php" class="btn-primary">Volver al inicio</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/whatsapp.php'; ?>
</body>

</html>