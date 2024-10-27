<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

$stmt_user = $conn->prepare("SELECT id FROM bank.users WHERE username=:username");
$stmt_user->bindParam(':username', $username);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Nie znaleziono użytkownika.");
}

$user_id = $user['id'];

// Pobieranie historii przelewów, gdzie użytkownik jest nadawcą lub odbiorcą
$sql_transfers = "SELECT t.*, u.username AS receiver_username 
                  FROM bank.transfers t 
                  JOIN bank.users u ON t.receiver_id = u.id 
                  WHERE t.sender_id=:user_id OR t.receiver_id=:user_id 
                  ORDER BY t.timestamp DESC";
$stmt_transfers = $conn->prepare($sql_transfers);
$stmt_transfers->bindParam(':user_id', $user_id);
$stmt_transfers->execute();
$result_transfers = $stmt_transfers->fetchAll(PDO::FETCH_ASSOC);

$conn = null;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Historia przelewów</title>
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
        .history-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
            width: 90%;
            max-width: 800px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #3498db;
            color: white;
        }
        td {
            background: white;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
        }
        a:hover {
            background: #2980b9;
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .history-container {
                padding: 20px;
            }
            table, th, td {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="history-container">
        <h1>Historia przelewów</h1>
        <table>
            <tr>
                <th>Data</th>
                <th>Odbiorca</th>
                <th>Kwota</th>
                <th>Typ</th>
            </tr>
            <?php if (!empty($result_transfers)): ?>
                <?php foreach ($result_transfers as $transfer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transfer['timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($transfer['receiver_username']); ?></td>
                        <td><?php echo htmlspecialchars($transfer['amount']); ?> PLN</td>
                        <td><?php echo $transfer['sender_id'] == $user_id ? 'Wysłany' : 'Odebrany'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Brak historii przelewów do wyświetlenia.</td>
                </tr>
            <?php endif; ?>
        </table>
        <a href="dashboard.php">Powrót</a>
    </div>
</body>
</html>
