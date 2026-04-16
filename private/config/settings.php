<?php
// private/config/settings.php

// 1. Configuración de Mercado Pago
// IMPORTANTE: Pedir al cliente sus credenciales de PRODUCCIÓN
define('MP_ACCESS_TOKEN', 'APP_USR-2016608383710992-041214-0a5f4b7332f489a8b5e658e8e7ce17eb-3331693746'); 
define('MP_PUBLIC_KEY', ''); 

// 2. Configuración de Correo (SMTP)
define('MAIL_HOST', 'mail.impresoscarnelli.com');
define('MAIL_USER', 'ventas@impresoscarnelli.com');
define('MAIL_PASS', 'Mauraska2026ImpresosCarnelli!');
define('MAIL_PORT', 465);
define('MAIL_ENCRYPTION', 'ssl'); // 'ssl' para puerto 465, 'tls' para 587
define('MAIL_FROM_EMAIL', 'ventas@impresoscarnelli.com');
define('MAIL_FROM_NAME', 'Impresos Carnelli Ventas');
define('MAIL_ADMIN_NOTIFICATIONS', 'agcarnelli2023@gmail.com');

// 3. URLs del Sistema
// Detectamos automáticamente la URL base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Limpiamos los folders de la API o carpetas internas si estamos siendo llamados desde ellas
$base_path = str_replace(['/api', '/private', '/public_html'], '', $script_path);
$base_path = rtrim($base_path, '/');
define('BASE_URL', $protocol . '://' . $host . $base_path);

// 4. API de Gestión (OT)
define('MANAGEMENT_API_URL', 'https://api.tuprogramadegestion.com/ots');

// 5. Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'u240116336_eCommerce');
define('DB_USER', 'u240116336_eCommerce');
define('DB_PASS', 'RszZ0cAXi0');
?>
