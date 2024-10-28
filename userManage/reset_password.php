<?php
session_start();
include 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $new_password = $_POST['new_password'];

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        $message = "<p class='error-message'>Hasło musi zawierać co najmniej 8 znaków, w tym jedną dużą literę, jedną cyfrę i jeden znak specjalny.</p>";
    } else {
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "SELECT * FROM bank.users WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $sql_update = "UPDATE bank.users SET password = :new_password WHERE id = :user_id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bindParam(':new_password', $new_password_hashed);
            $stmt_update->bindParam(':user_id', $user['id']);

            if ($stmt_update->execute()) {
                $message = "<p class='success-message'>Hasło zostało zaktualizowane. Przekierowanie na stronę logowania...</p>";
                header("refresh:3;url=login.php");
            } else {
                $message = "<p class='error-message'>Nie udało się zaktualizować hasła.</p>";
            }
        } else {
            $message = "<p class='error-message'>Nie znaleziono użytkownika o podanej nazwie użytkownika.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zresetuj Hasło</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2ecc71, #3498db);
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
            background: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }
        button:hover {
            background: #27ae60;
        }
        a {
            display: block;
            margin-top: 1rem;
            color: #2ecc71;
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: #27ae60;
        }
        .success-message {
            color: #27ae60;
            font-weight: bold;
        }
        .error-message {
            color: #e74c3c;
            font-weight: bold;
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
        <h1>Zresetuj Hasło</h1>
        <?php echo $message; ?>
        <form action="reset_password.php" method="POST">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>
            <label for="new_password">Nowe Hasło:</label>
            <input type="password" id="new_password" name="new_password" required>
            <button type="submit">Zresetuj</button>
        </form>
        <a href="index.html">Powrót na stronę główną</a>
    </div>
</body>
</html>
