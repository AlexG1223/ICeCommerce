<?php
$c = new mysqli('localhost', 'u240116336_eCommerce', 'RszZ0cAXi0', 'u240116336_eCommerce');
if ($c->connect_error) die("Connection failed: " . $c->connect_error);
$r = $c->query('SHOW TABLES');
while($row = $r->fetch_row()) echo $row[0].PHP_EOL;
