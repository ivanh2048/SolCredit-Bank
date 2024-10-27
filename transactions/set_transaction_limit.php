<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
    exit();
}

$username = $_SESSION['username'];
$new_limit = $_POST['new_limit'];

if (is_numeric($new_limit) && $new_limit > 0) {
    try {
        $sql = "UPDATE bank.users SET transaction_limit = :new_limit WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':new_limit', $new_limit);
        $stmt->bindParam(':username', $username);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Limit transakcji został pomyślnie ustawiony.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Wystąpił błąd podczas ustawiania limitu.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Błąd bazy danych: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowy limit.']);
}
?>
