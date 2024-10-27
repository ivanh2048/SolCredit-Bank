<?php
session_start();
include 'config.php';

$message = "";

// Генерація CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<p style='color: red;'>Błąd: niepoprawny token CSRF.</p>";
    } else {
        $sender_username = $_SESSION['username'];
        $receiver_card_number = htmlspecialchars($_POST['receiver_card_number']);
        $amount = htmlspecialchars($_POST['amount']);

        // Валідація суми переказу
        if (!is_numeric($amount) || $amount <= 0) {
            $message = "<p style='color: red;'>Błąd: kwota przelewu musi być większa niż zero i poprawnie sformatowana.</p>";
        } else {
            // Pobieranie danych nadawcy
            $sql_sender = "SELECT id, balance FROM bank.users WHERE username=:username";
            $stmt_sender = $conn->prepare($sql_sender);
            $stmt_sender->bindParam(':username', $sender_username);
            $stmt_sender->execute();
            $sender_data = $stmt_sender->fetch(PDO::FETCH_ASSOC);

            if (!$sender_data) {
                $message = "<p style='color: red;'>Błąd: nieznany nadawca.</p>";
            } else {
                // Sprawdzenie czy odbiorca istnieje (po numerze karty)
                $sql_receiver = "SELECT id, balance FROM bank.users WHERE card_number=:card_number";
                $stmt_receiver = $conn->prepare($sql_receiver);
                $stmt_receiver->bindParam(':card_number', $receiver_card_number);
                $stmt_receiver->execute();
                $receiver_data = $stmt_receiver->fetch(PDO::FETCH_ASSOC);

                if (!$receiver_data) {
                    $message = "<p style='color: red;'>Błąd: nieznany odbiorca.</p>";
                } elseif ($sender_data['balance'] < $amount) {
                    $message = "<p style='color: red;'>Błąd: niewystarczające środki na koncie nadawcy.</p>";
                } else {
                    // Obliczenie nowych sald
                    $new_sender_balance = $sender_data['balance'] - $amount;
                    $new_receiver_balance = $receiver_data['balance'] + $amount;

                    // Rozpoczęcie transakcji
                    try {
                        $conn->beginTransaction();

                        // Aktualizacja salda nadawcy
                        $sql_update_sender = "UPDATE bank.users SET balance=:balance WHERE id=:id";
                        $stmt_update_sender = $conn->prepare($sql_update_sender);
                        $stmt_update_sender->bindParam(':balance', $new_sender_balance);
                        $stmt_update_sender->bindParam(':id', $sender_data['id']);
                        $stmt_update_sender->execute();

                        // Aktualizacja salda odbiorcy
                        $sql_update_receiver = "UPDATE bank.users SET balance=:balance WHERE id=:id";
                        $stmt_update_receiver = $conn->prepare($sql_update_receiver);
                        $stmt_update_receiver->bindParam(':balance', $new_receiver_balance);
                        $stmt_update_receiver->bindParam(':id', $receiver_data['id']);
                        $stmt_update_receiver->execute();

                        // Zapisanie przelewu do historii
                        $sql_transfer = "INSERT INTO bank.transfers (sender_id, receiver_id, receiver_card_number, amount, timestamp) VALUES (:sender_id, :receiver_id, :receiver_card_number, :amount, NOW())";
                        $stmt_transfer = $conn->prepare($sql_transfer);
                        $stmt_transfer->bindParam(':sender_id', $sender_data['id']);
                        $stmt_transfer->bindParam(':receiver_id', $receiver_data['id']);
                        $stmt_transfer->bindParam(':receiver_card_number', $receiver_card_number);
                        $stmt_transfer->bindParam(':amount', $amount);
                        $stmt_transfer->execute();

                        $conn->commit();
                        $message = "<p style='color: green;'>Przelew wykonany pomyślnie. Przekierowanie...</p>";
                        header("refresh:3;url=dashboard.php");
                    } catch (PDOException $e) {
                        $conn->rollBack();
                        $message = "<p style='color: red;'>Błąd podczas przetwarzania transakcji: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wykonaj Przelew</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .transfer-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }
        label {
            text-align: left;
            color: #2c3e50;
        }
        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #2980b9;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="transfer-container">
        <h1>Wykonaj Przelew</h1>
        <?php echo $message; ?>
        <form action="transfer.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <label for="receiver_card_number">Numer karty odbiorcy:</label>
            <input type="text" id="receiver_card_number" name="receiver_card_number" required>
            <label for="amount">Kwota:</label>
            <input type="number" id="amount" name="amount" step="0.01" required>
            <button type="submit">Wykonaj przelew</button>
        </form>
        <a href="dashboard.php">Powrót</a>
    </div>
</body>
</html>
