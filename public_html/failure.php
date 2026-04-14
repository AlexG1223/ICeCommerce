<?php
// failure.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Fallido - Impresos Carnelli</title>
    <link rel="stylesheet" href="globals/main.css">
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
            <a href="index.php" class="logo">IMPRESOS CARNELLI</a>
        </div>
    </header>

    <div class="container">
        <div class="result-container">
            <div class="icon-failure">✗</div>
            <h1>Pago Fallido</h1>
            <p>Ocurrió un problema al procesar tu pago. Por favor, intenta nuevamente más tarde o utiliza otro método de pago.</p>
            <a href="index.php" class="btn-primary">Volver al inicio</a>
        </div>
    </div>
</body>
</html>
