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

    default:
        echo json_encode(['status' => 'error', 'message' => 'Nieznane działanie']);

        case 'create_admin':
            $username = $_POST['username'];
            $password = $_POST['password'];

            if (strlen($username) < 3) {
                echo json_encode(['status' => 'error', 'message' => 'Nazwa użytkownika musi mieć co najmniej 3 znaki.']);
                break;
            }

            $stmt = $conn->prepare("SELECT * FROM bank.users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode(['status' => 'error', 'message' => 'Nazwa użytkownika już istnieje.']);
                break;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO bank.users (username, password, role) VALUES (:username, :password, 'admin')");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Konto administratora zostało utworzone.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Wystąpił błąd podczas tworzenia konta.']);
            }
            break;
        
}
?>
