<?php
// Midlertidig engangsoppsett. Ikke legg passord i GitHub.
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
$path = __DIR__ . '/config.local.php';
if (is_file($path)) {
    http_response_code(403);
    exit('Databasekonfigurasjonen finnes allerede. Oppsett er deaktivert.');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim((string)($_POST['host'] ?? ''));
    $dbname = trim((string)($_POST['dbname'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($host === '' || $dbname === '' || $username === '' || $password === '') {
        $error = 'Fyll ut alle feltene.';
    } else {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $test = new mysqli($host, $username, $password, $dbname);
            $test->set_charset('utf8mb4');
            $test->close();
            $content = "<?php\nreturn " . var_export([
                'host' => $host,
                'dbname' => $dbname,
                'username' => $username,
                'password' => $password,
            ], true) . ";\n";
            // Exclusive creation: do not overwrite local config if it appears meanwhile.
            $handle = @fopen($path, 'x');
            if ($handle === false) {
                $error = 'Kan ikke opprette config.local.php på serveren (mappen er muligens skrivebeskyttet).';
            } else {
                @chmod($path, 0600);
                $written = fwrite($handle, $content);
                fclose($handle);
                if ($written !== strlen($content)) {
                    @unlink($path);
                    $error = 'Kunne ikke lagre innstillingene.';
                } else {
                    // Remove the publicly accessible installer after success.
                    @unlink(__FILE__);
                    header('Location: ./', true, 303);
                    exit;
                }
            }
        } catch (mysqli_sql_exception $e) {
            $error = 'Databasefeil: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Engangsoppsett av database</title></head>
<body style="font:16px system-ui;max-width:480px;margin:40px auto;padding:15px">
<h1>Engangsoppsett av database</h1>
<p>Opplysningene sendes til serveren og lagres bare i config.local.php der. Siden prøver å slette seg selv ved vellykket oppsett.</p>
<?php if ($error !== ''): ?><p style="color:#ad1820"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p><?php endif; ?>
<form method="post" autocomplete="off">
<label>Databasevert<br><input name="host" required style="width:100%" placeholder="Vertsnavn fra USN"></label><br><br>
<label>Databasenavn<br><input name="dbname" required style="width:100%"></label><br><br>
<label>Databasebruker<br><input name="username" required style="width:100%"></label><br><br>
<label>Databasepassord<br><input type="password" name="password" required style="width:100%"></label><br><br>
<button type="submit">Test kobling og fullfør oppsett</button>
</form></body></html>
