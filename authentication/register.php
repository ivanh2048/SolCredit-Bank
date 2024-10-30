<?php
session_start();
include '../bank-app/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function generateCardNumber($conn) {
    do {
        $randomDigits = mt_rand(1000000000000000, 9999999999999999);
        $stmt = $conn->prepare("SELECT * FROM bank.users WHERE card_number = ?");
        $stmt->bindParam(1, $randomDigits);
        $stmt->execute();
        $existingCard = $stmt->fetch(PDO::FETCH_ASSOC);
    } while ($existingCard);

    return $randomDigits;
}

function generateExpiryDate() {
    $currentYear = date('Y');
    $currentMonth = date('m');

    $expiryYear = $currentYear + rand(3, 5);
    $expiryMonth = rand(1, 12);
    $expiryMonthFormatted = str_pad($expiryMonth, 2, '0', STR_PAD_LEFT);

    return $expiryMonthFormatted . '/' . substr($expiryYear, 2);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $confirm_password = $_POST['confirm_password'];
    $role = 'user';
    $activated = 1; 
    $card_number = generateCardNumber($conn);
    $expiry_date = generateExpiryDate(); 
    $initial_balance = 100.00; 
    $verification_code = rand(100000, 999999); 

   
    if (!password_verify($confirm_password, $password)) {
        $message = '<span class="error-message">Hasła nie pasują do siebie.</span>';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
        $message = '<span class="error-message">Hasło nie spełnia wymagań bezpieczeństwa.</span>';
    } else {
        $stmt = $conn->prepare("SELECT * FROM bank.users WHERE username = ? OR email = ?");
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $message = '<span class="error-message">Nazwa użytkownika lub email już istnieje.</span>';
        } else {
            $stmt = $conn->prepare("INSERT INTO bank.users (username, email, password, role, activated, card_number, expiry_date, balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $password);
            $stmt->bindParam(4, $role);
            $stmt->bindParam(5, $activated);
            $stmt->bindParam(6, $card_number);
            $stmt->bindParam(7, $expiry_date);
            $stmt->bindParam(8, $initial_balance);

            if ($stmt->execute()) {
                sendVerificationEmail($email, $verification_code);
                $_SESSION['success_message'] = 'Rejestracja udana. Sprawdź swoją skrzynkę mailową, aby otrzymać kod potwierdzenia.';
                header('Location: login.php');
                exit();
            } else {
                $message = '<span class="error-message">Błąd podczas rejestracji.</span>';
            }
        }
    }
}

function sendVerificationEmail($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'grabovskyivan78@gmail.com';
        $mail->Password   = 'cqyi llbe cfvx jzgk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('grabovskyivan78@gmail.com', 'SolCredit');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Kod potwierdzenia rejestracji - SolCredit';
        $mail->Body    = 'Twój kod potwierdzenia to: ' . $code;

        $mail->send();
    } catch (Exception $e) {
        echo "Błąd podczas wysyłania maila: {$mail->ErrorInfo}";
    }
}

?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <script>
        function validatePassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorMessage = document.getElementById('password-error');
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!regex.test(password)) {
                errorMessage.textContent = 'Hasło musi zawierać co najmniej 8 znaków, w tym jedną dużą literę, jedną cyfrę i jeden znak specjalny.';
                return false;
            }

            if (password !== confirmPassword) {
                errorMessage.textContent = 'Hasła nie pasują do siebie.';
                return false;
            }

            errorMessage.textContent = '';
            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                if (!validatePassword()) {
                    event.preventDefault();
                }
            });
        });

        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            fetch('check_username.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `username=${username}`
            })
            .then(response => response.json())
            .then(data => {
                const message = document.getElementById('username-error');
                if (data.exists) {
                    message.textContent = 'Nazwa użytkownika już istnieje';
                    message.style.color = 'red';
                } else {
                    message.textContent = '';
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Rejestracja</h1>
        <form action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="username"><i class="fa fa-user"></i> Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>
            <div id="username-error"></div>
            <label for="email"><i class="fa fa-envelope"></i> Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password"><i class="fa fa-lock"></i> Hasło:</label>
            <input type="password" id="password" name="password" required>
            <label for="confirm_password"><i class="fa fa-lock"></i> Potwierdź hasło:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <div id="password-error" style="color: red;"></div>
        
            <select id="role" name="role" class="styled-select">
                <option value="user">Użytkownik</option>
                <option value="admin">Administrator</option>
            </select>

        <button type="submit">Zarejestruj się</button>
        </form>

        <a href="/bank-app/index.html">Powrót na stronę główną</a>
        <div class="message">
            <?php echo $message; ?>
        </div>
        <script src="/assets/script_interfejs.js"></script>
    </div>
</body>
</html>