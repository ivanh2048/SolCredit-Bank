<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
    exit();
}

$username = $_SESSION['username'];
$lock_status = $_POST['lock_status']; // "lock" або "unlock"

try {
    $card_locked = ($lock_status === "lock") ? 1 : 0;
    $sql = "UPDATE bank.users SET card_locked = :card_locked WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':card_locked', $card_locked);
    $stmt->bindParam(':username', $username);
    if ($stmt->execute()) {
        $message = $card_locked ? 'Karta została zablokowana.' : 'Karta została odblokowana.';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Wystąpił błąd podczas zmiany statusu karty.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd bazy danych: ' . $e->getMessage()]);
}
?>
