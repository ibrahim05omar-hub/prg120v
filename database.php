<?php
// Lokalt brukes config.local.php. På Dokploy brukes miljøvariabler.
$configPath = __DIR__ . '/config.local.php';
$config = is_file($configPath)
    ? require $configPath
    : [
        'host' => getenv('DB_HOST'),
        'dbname' => getenv('DB_NAME'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASSWORD'),
    ];

if (!$config['host'] || !$config['dbname'] || !$config['username'] || $config['password'] === false) {
    http_response_code(500);
    exit('Databaseinnstillingene mangler på serveren.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Kunne ikke koble til databasen.');
}
