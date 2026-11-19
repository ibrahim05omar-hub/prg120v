<?php
echo "PHP: " . PHP_VERSION . "<pre>";
echo "PDO: " . (extension_loaded('pdo') ? 'JA' : 'NEI') . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'JA' : 'NEI') . "\n";
echo "MySQLi: " . (extension_loaded('mysqli') ? 'JA' : 'NEI') . "\n";
echo "</pre>";