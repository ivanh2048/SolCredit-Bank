<?php
session_start();
include 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($username && $password) {
        try {
            $sql = "SELECT * FROM bank.users WHERE username = :username AND email_verified = TRUE";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $user['role'];
                session_regenerate_id(true);
                header('Location: dashboard.php');
                exit();
            } else {
                $message = 'Nieprawidłowa nazwa użytkownika lub hasło.';
            }
        } catch (PDOException $e) {
            $message = 'Błąd połączenia z bazą danych: ' . $e->getMessage();
        }
    } else {
        $message = 'Proszę wprowadzić nazwę użytkownika i hasło.';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .container {
            background: rgba(33, 45, 72, 0.8);
            border-radius: 15px;
            padding: 2rem;
            width: 320px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            margin-bottom: 1rem;
            color: #fff;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
            font-size: 2rem;
            font-weight: bold;
        }

        label {
            display: block;
            margin: 0.5rem 0;
            color: #ccd1e4;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            font-size: 0.9rem;
            background: rgba(43, 51, 74, 0.7);
            color: #fff;
            box-shadow: 0 4px 8px rgba(50, 60, 80, 0.4);
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(100, 150, 255, 0.6), 0 0 10px #2a5298;
        }

        button {
            padding: 0.5rem 2rem;
            background: linear-gradient(45deg, #2a5298, #1e3c72);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.5s ease, transform 0.2s;
            box-shadow: 0 8px 15px rgba(32, 42, 66, 0.8), 0 6px 20px rgba(44, 64, 98, 0.7);
        }

        button:hover {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            transform: scale(1.05);
        }

        .message {
            margin-top: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            text-shadow: 0 1px 15px rgba(255, 255, 255, 0.3);
            color: #fff;
            background: rgba(231, 76, 60, 0.8);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Logowanie</h1>
        <form action="/authentication/login_after_verification.php" method="POST">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Zaloguj się</button>
        </form>
        <?php if ($message): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
