<?php
// private/config/settings.php

// 1. Configuración de Mercado Pago
// IMPORTANTE: Pedir al cliente sus credenciales de PRODUCCIÓN
define('MP_ACCESS_TOKEN', 'APP_USR-4411227484068291-041619-3c00c1b439f8ac4916def2b76f62c402-1557406552'); 
define('MP_PUBLIC_KEY', 'APP_USR-013208fd-49e4-493b-bec9-fe9e89d30531'); 

// 2. Configuración de Correo (SMTP)
define('MAIL_HOST', 'smtp.hostinger.com');
define('MAIL_USER', 'ventas@impresoscarnelli.com');
define('MAIL_PASS', 'Mauraska2026ImpresosCarnelli!');
define('MAIL_PORT', 465);
define('MAIL_ENCRYPTION', 'ssl'); // 'ssl' para puerto 465, 'tls' para 587
define('MAIL_FROM_EMAIL', 'ventas@impresoscarnelli.com');
define('MAIL_FROM_NAME', 'Impresos Carnelli Ventas');
define('MAIL_ADMIN_NOTIFICATIONS', 'agcarnelli2023@gmail.com');

// 3. URLs del Sistema
// Detectamos automáticamente la URL base
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$script_path = str_replace('\\', '/', dirname($script_name));
// Limpiamos los folders de la API o carpetas internas si estamos siendo llamados desde ellas
$base_path = str_replace(['/api', '/private', '/public_html'], '', $script_path);
$base_path = rtrim($base_path, '/');
define('BASE_URL', $protocol . '://' . $host . $base_path);


// 4. API de Gestión (OT)
// 4. API de Gestión (OT)
define('MANAGEMENT_API_URL', 'https://impresoscarnelli.com/public/api/ordenes_trabajo/create.php');
define('MANAGEMENT_API_KEY', 'IC_SECRET_2026_EC');

// 5. Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'u240116336_eCommerce');
define('DB_USER', 'u240116336_eCommerce');
define('DB_PASS', 'RszZ0cAXi0');
?>
