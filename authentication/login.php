<?php
session_start();
include '../config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($username && $password) {
        try {
            $sql = "SELECT * FROM bank.users WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if (!$user['email_verified']) {
                    $_SESSION['message'] = 'Musisz potwierdzić swój adres email przed zalogowaniem.';
                    header('Location: verify_account.php');
                    exit();
                }

                $_SESSION['username'] = $username;
                $_SESSION['role'] = $user['role'];
                session_regenerate_id(true);
                $_SESSION['message'] = 'Logowanie przeszło bez żadnych problemów.';
                header('Location: dashboard.php');
                exit();
            } else {
                $_SESSION['message'] = 'Nieprawidłowe hasło lub konto.';
                header('Location: login.php');
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Błąd połączenia z bazą danych: ' . $e->getMessage();
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['message'] = 'Proszę wprowadzić nazwę użytkownika i hasło.';
        header('Location: login.php');
        exit();
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

        a {
            display: block;
            margin-top: 1rem;
            color: #ccd1e4;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        a:hover {
            color: #a4b0d1;
        }

        .message {
            margin-top: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            text-shadow: 0 1px 15px rgba(255, 255, 255, 0.3);
        }

        .error-message {
            color: #fff;
            background: rgba(231, 76, 60, 0.8);
        }

        .success-message {
            color: #fff;
            background: rgba(46, 204, 113, 0.8);
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

        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.8rem;
            }

            button {
                padding: 0.5rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Logowanie</h1>
        <form action="/authentication/login.php" method="POST">
        <label for="username"><i class="fa fa-user"></i> Nazwa użytkownika:</label>
        <input type="text" id="username" name="username" required>
        <label for="password"><i class="fa fa-lock"></i> Hasło:</label>
        <input type="password" id="password" name="password" required>
        <label for="verification_code"><i class="fa fa-key"></i> Kod potwierdzenia:</label>
        <input type="text" id="verification_code" name="verification_code" required>
        <button type="submit">Zaloguj się</button>
        </form>
        <a href="/userManage/reset_password.php">Zapomniałem hasła</a>
        <a href="/bank-app/index.html">Powrót na stronę główną</a>
        <div class="message">
            <?php
            if (isset($_SESSION['message'])) {
                echo '<span class="error-message">' . $_SESSION['message'] . '</span>';
                unset($_SESSION['message']);
            }
            ?>
        </div>
    </div>
</body>
</html>
