<?php
session_start();
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../phpmailer/vendor/autoload.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['resend'])) {
        // Повторна відправка коду
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($username) {
            try {
                $stmt = $conn->prepare("SELECT * FROM bank.users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $verification_code = rand(100000, 999999);
                    $stmt = $conn->prepare("UPDATE bank.users SET verification_code = :code WHERE username = :username");
                    $stmt->bindParam(':code', $verification_code);
                    $stmt->bindParam(':username', $username);
                    $stmt->execute();

                    // Використання PHPMailer для надсилання нового коду
                    $mail = new PHPMailer(true);
                    try {
                        // Налаштування сервера
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'grabovskyivan78@gmail.com'; // Замість цього вкажіть свій email
                        $mail->Password   = 'cqyi llbe cfvx jzgk'; // Замість цього вкажіть свій пароль або пароль додатку
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        // Одержувачі
                        $mail->setFrom('grabovskyivan78@gmail.com', 'SolCredit');
                        $mail->addAddress($user['email']);

                        // Зміст листа
                        $mail->isHTML(true);
                        $mail->Subject = 'Twój kod weryfikacyjny - SolCredit';
                        $mail->Body    = 'Twój nowy kod weryfikacyjny to: ' . $verification_code;

                        $mail->send();
                        $message = '<span class="success-message">Kod weryfikacyjny został ponownie wysłany.</span>';
                    } catch (Exception $e) {
                        $message = '<span class="error-message">Błąd podczas wysyłania wiadomości: ' . $mail->ErrorInfo . '</span>';
                    }
                } else {
                    $message = '<span class="error-message">Nieprawidłowa nazwa użytkownika.</span>';
                }
            } catch (Exception $e) {
                $message = '<span class="error-message">Błąd podczas ponownego wysyłania kodu: ' . $e->getMessage() . '</span>';
            }
        } else {
            $message = '<span class="error-message">Proszę podać nazwę użytkownika.</span>';
        }
    } else {
        // Підтвердження аккаунта
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        $verification_code = filter_input(INPUT_POST, 'verification_code', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($username && $verification_code) {
            $stmt = $conn->prepare("SELECT * FROM bank.users WHERE username = :username AND verification_code = :code");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':code', $verification_code);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $stmt = $conn->prepare("UPDATE bank.users SET email_verified = TRUE, verification_code = NULL WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $message = '<span class="success-message">Konto zostało pomyślnie potwierdzone.</span>';
                
                // Перенаправлення на сторінку логування без коду підтвердження
                header('Location: login_after_verification.php');
                exit();
            }
             else {
                $message = '<span class="error-message">Nieprawidłowy kod weryfikacyjny.</span>';
            }
        } else {
            $message = '<span class="error-message">Proszę wprowadzić nazwę użytkownika i kod weryfikacyjny.</span>';
        }
    }
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potwierdzenie konta</title>
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

        .verify-container {
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

        h2 {
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
            margin-bottom: 10px;
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
        }

        .error-message {
            color: #fff;
            background: rgba(231, 76, 60, 0.8);
        }

        .success-message {
            color: #fff;
            background: rgba(46, 204, 113, 0.8);
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <h2>Potwierdzenie konta</h2>
        <form action="verify_account.php" method="POST">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>
            <label for="verification_code">Kod weryfikacyjny:</label>
            <input type="text" id="verification_code" name="verification_code" required>
            <button type="submit" name="verify">Potwierdź</button>
            <button type="submit" name="resend">Wyślij kod ponownie</button>
        </form>
        <div class="message">
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>
