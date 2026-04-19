<?php
$c = new mysqli('localhost', 'u240116336_eCommerce', 'RszZ0cAXi0', 'u240116336_eCommerce');
$r = $c->query('DESCRIBE orders');
while($row = $r->fetch_assoc()) echo $row['Field'] . " (" . $row['Type'] . ")".PHP_EOL;
