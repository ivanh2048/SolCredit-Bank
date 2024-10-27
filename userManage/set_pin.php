<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['set_pin'])) {
    header('Location: login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pin = filter_input(INPUT_POST, 'pin', FILTER_SANITIZE_STRING);

    if ($pin && strlen($pin) == 4 && ctype_digit($pin)) {
        try {
            $username = $_SESSION['username'];
            $sql = "UPDATE bank.users SET pin_code = :pin WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':pin', $pin);
            $stmt->bindParam(':username', $username);
            if ($stmt->execute()) {
                unset($_SESSION['set_pin']);
                $_SESSION['message'] = 'PIN został pomyślnie ustawiony.';
                header('Location: dashboard.php');
                exit();
            } else {
                $message = 'Wystąpił błąd podczas ustawiania PIN-u.';
            }
        } catch (PDOException $e) {
            $message = 'Błąd połączenia z bazą danych: ' . $e->getMessage();
        }
    } else {
        $message = 'PIN musi mieć 4 cyfry.';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Ustaw PIN</title>
</head>
<body>
    <h1>Ustaw swój PIN</h1>
    <form method="post" action="set_pin.php">
        <label for="pin">PIN (4 cyfry):</label>
        <input type="text" name="pin" id="pin" maxlength="4" required>
        <button type="submit">Ustaw PIN</button>
    </form>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</body>
</html>
