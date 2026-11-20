<?php
require 'database.php';

$melding = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registrer_klasse'])) {
        $klassekode = trim($_POST['klassekode'] ?? '');
        $klassenavn = trim($_POST['klassenavn'] ?? '');
        $studiumkode = trim($_POST['studiumkode'] ?? '');

        if ($klassekode !== '' && $klassenavn !== '' && $studiumkode !== '') {
            try {
                $stmt = $db->prepare('INSERT INTO klasse (klassekode, klassenavn, studiumkode) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $klassekode, $klassenavn, $studiumkode);
                $stmt->execute();
                $melding = 'Klassen ble registrert.';
            } catch (mysqli_sql_exception $e) {
                $melding = 'Kunne ikke registrere klassen. Sjekk om klassekoden allerede finnes.';
            }
        } else {
            $melding = 'Fyll ut alle feltene.';
        }
    } elseif (isset($_POST['slett_klasse'])) {
        $klassekode = trim($_POST['klassekode'] ?? '');
        try {
            $stmt = $db->prepare('DELETE FROM klasse WHERE klassekode = ?');
            $stmt->bind_param('s', $klassekode);
            $stmt->execute();
            $melding = 'Klassen ble slettet.';
        } catch (mysqli_sql_exception $e) {
            $melding = 'Klassen kan ikke slettes hvis den har studenter.';
        }
    } elseif (isset($_POST['registrer_student'])) {
        $brukernavn = trim($_POST['brukernavn'] ?? '');
        $fornavn = trim($_POST['fornavn'] ?? '');
        $etternavn = trim($_POST['etternavn'] ?? '');
        $klassekode = trim($_POST['student_klassekode'] ?? '');

        if ($brukernavn !== '' && $fornavn !== '' && $etternavn !== '' && $klassekode !== '') {
            try {
                $stmt = $db->prepare('INSERT INTO student (brukernavn, fornavn, etternavn, klassekode) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $brukernavn, $fornavn, $etternavn, $klassekode);
                $stmt->execute();
                $melding = 'Studenten ble registrert.';
            } catch (mysqli_sql_exception $e) {
                $melding = 'Kunne ikke registrere studenten. Sjekk brukernavn og klasse.';
            }
        } else {
            $melding = 'Fyll ut alle feltene.';
        }
    } elseif (isset($_POST['slett_student'])) {
        $brukernavn = trim($_POST['brukernavn'] ?? '');
        try {
            $stmt = $db->prepare('DELETE FROM student WHERE brukernavn = ?');
            $stmt->bind_param('s', $brukernavn);
            $stmt->execute();
            $melding = 'Studenten ble slettet.';
        } catch (mysqli_sql_exception $e) {
            $melding = 'Kunne ikke slette studenten.';
        }
    }
}

$klasser = $db->query('SELECT * FROM klasse ORDER BY klassekode')->fetch_all(MYSQLI_ASSOC);
$studenter = $db->query('SELECT * FROM student ORDER BY brukernavn')->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Studentadministrasjon</title>
</head>
<body>

<h1>Studentadministrasjon</h1>


<h2>Registrer klasse</h2>

<?php if (!empty($melding)): ?>
    <p><?= htmlspecialchars($melding) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Klassekode:</label>
    <input type="text" name="klassekode" maxlength="5" required>

    <label>Klassenavn:</label>
    <input type="text" name="klassenavn" maxlength="50" required>

    <label>Studiumkode:</label>
    <input type="text" name="studiumkode" maxlength="50" required>

    <button type="submit" name="registrer_klasse" value="1">
        Registrer klasse
    </button>
</form>

<h2>Alle klasser</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>Klassekode</th>
        <th>Klassenavn</th>
        <th>Studiumkode</th>
        <th>Handling</th>
    </tr>

    <?php foreach ($klasser as $klasse): ?>
    <tr>
        <td><?= htmlspecialchars($klasse['klassekode']) ?></td>
        <td><?= htmlspecialchars($klasse['klassenavn']) ?></td>
        <td><?= htmlspecialchars($klasse['studiumkode']) ?></td>

<td>
    <form method="POST">
        <input type="hidden" name="klassekode"
               value="<?= htmlspecialchars($klasse['klassekode']) ?>">
        <button type="submit" name="slett_klasse" value="1">
            Slett
        </button>
    </form>
</td>

    </tr>
    <?php endforeach; ?>
</table>

<h2>Registrer student</h2>

<form method="POST">
    <label>Brukernavn:</label>
    <input type="text" name="brukernavn" maxlength="7" required>

    <label>Fornavn:</label>
    <input type="text" name="fornavn" maxlength="50" required>

    <label>Etternavn:</label>
    <input type="text" name="etternavn" maxlength="50" required>

    <label>Klasse:</label>
    <select name="student_klassekode" required>
        <option value="">Velg klasse</option>
        <?php foreach ($klasser as $klasse): ?>
            <option value="<?= htmlspecialchars($klasse['klassekode']) ?>">
                <?= htmlspecialchars($klasse['klassekode']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="registrer_student" value="1">
        Registrer student
    </button>
</form>

<h2>Alle studenter</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>Brukernavn</th>
        <th>Fornavn</th>
        <th>Etternavn</th>
        <th>Klasse</th>
        <th>Handling</th>
    </tr>

    <?php foreach ($studenter as $student): ?>
    <tr>
        <td><?= htmlspecialchars($student['brukernavn']) ?></td>
        <td><?= htmlspecialchars($student['fornavn']) ?></td>
        <td><?= htmlspecialchars($student['etternavn']) ?></td>
        <td><?= htmlspecialchars($student['klassekode']) ?></td>

<td>
    <form method="POST">
        <input type="hidden" name="brukernavn"
               value="<?= htmlspecialchars($student['brukernavn']) ?>">
        <button type="submit" name="slett_student" value="1">
            Slett
        </button>
    </form>
</td>

    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
