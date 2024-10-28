<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = htmlspecialchars($_POST['username_or_email']);
    $new_password = $_POST['new_password'];

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        echo '<span class="error-message">Hasło musi zawierać co najmniej 8 znaków, w tym jedną dużą literę, jedną cyfrę i jeden znak specjalny.</span>';
    } else {
        $sql = "SELECT * FROM bank.users WHERE username=? OR email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $username_or_email);
        $stmt->bindParam(2, $username_or_email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE bank.users SET password=? WHERE id=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bindParam(1, $hashed_password);
            $stmt_update->bindParam(2, $user['id']);

            if ($stmt_update->execute()) {
                echo '<span class="success-message">Hasło zostało zresetowane pomyślnie.</span>';
                header('Refresh: 3; URL=login.php');
                exit();
            } else {
                echo '<span class="error-message">Błąd podczas resetowania hasła.</span>';
            }
        } else {
            echo '<span class="error-message">Nie znaleziono użytkownika o podanej nazwie użytkownika lub adresie email.</span>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Resetowanie hasła</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 300px;
            animation: fadeIn 1s ease-in-out;
        }
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        label {
            display: block;
            margin: 0.5rem 0;
            color: #333;
        }
        input {
            width: calc(100% - 20px);
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            padding: 0.5rem 2rem;
            background: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }
        button:hover {
            background: #1c2833;
        }
        a {
            display: block;
            margin-top: 1rem;
            color: #2c3e50;
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: #1c2833;
        }
        .message {
            margin-top: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
        }
        .error-message {
            color: #fff;
            background: #e74c3c;
        }
        .success-message {
            color: #fff;
            background: #2ecc71;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Resetowanie hasła</h1>
        <form action="reset_password_process.php" method="POST">
            <label for="username_or_email">Nazwa użytkownika lub email:</label>
            <input type="text" id="username_or_email" name="username_or_email" required>
            <label for="new_password">Nowe hasło:</label>
            <input type="password" id="new_password" name="new_password" required>
            <button type="submit">Resetuj hasło</button>
        </form>
        <a href="login.php">Powrót na stronę logowania</a>
        <div class="message">
            <?php if (isset($message)) { echo $message; } ?>
        </div>
    </div>
</body>
</html>
