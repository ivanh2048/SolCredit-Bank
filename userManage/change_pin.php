<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
    exit();
}

$username = $_SESSION['username'];
$new_pin = $_POST['new_pin'];

if (strlen($new_pin) === 4 && is_numeric($new_pin)) {
    try {
        $sql = "UPDATE bank.users SET pin_code = :new_pin WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':new_pin', $new_pin);
        $stmt->bindParam(':username', $username);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'PIN został pomyślnie zmieniony.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Wystąpił błąd podczas zmiany PIN.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Błąd bazy danych: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowy PIN.']);
}
?>
