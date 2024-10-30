<?php
session_start();
include '../bank-app/config.php';

if (!isset($_SESSION['user_id'])) {
    error_log("User ID not found in session. Session data: " . print_r($_SESSION, true));
    die(json_encode(['status' => 'error', 'message' => 'Nieobecne uwierzytelnienie']));
}

$action = $_POST['action'] ?? ''; 

switch ($action) {
    case 'change_pin':
        $newPin = $_POST['new_pin'];
        if (strlen($newPin) === 4 && is_numeric($newPin)) {
            $stmt = $conn->prepare("UPDATE bank.users SET pin_code = ? WHERE id = ?");
            $stmt->execute([$newPin, $_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'PIN został zmieniony']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowy format PIN']);
        }
        break;

    case 'toggle_card_lock':
        $lockStatus = $_POST['lock_status'];
        $isLocked = ($lockStatus === 'lock') ? true : false;
        $stmt = $conn->prepare("UPDATE bank.users SET card_locked = ? WHERE id = ?");
        $stmt->execute([$isLocked, $_SESSION['user_id']]);
        echo json_encode(['status' => 'success', 'message' => 'Status karty został zmieniony']);
        break;

    case 'set_transaction_limit':
        $newLimit = $_POST['new_limit'];
        if (is_numeric($newLimit)) {
            $stmt = $conn->prepare("UPDATE bank.users SET transaction_limit = ? WHERE id = ?");
            $stmt->execute([$newLimit, $_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Limit transakcji został ustawiony']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowy format limitu']);
        }
        break;

        case 'create_admin':
            $username = htmlspecialchars($_POST['username']);
            $email = htmlspecialchars($_POST['email']); 
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
            $stmt = $conn->prepare("SELECT * FROM bank.users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($existingUser) {
                echo json_encode(['status' => 'error', 'message' => 'Nazwa użytkownika lub e-mail już istnieje']);
            } else {
                $stmt = $conn->prepare("INSERT INTO bank.users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                if ($stmt->execute([$username, $email, $password])) {
                    echo json_encode(['status' => 'success', 'message' => 'Konto administratora zostało utworzone']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Błąd podczas tworzenia konta']);
                }
            }
            break;
        
}
?>
