<?php
session_start();
include '../bank-app/config.php';

// Sprawdzenie, czy użytkownik jest zalogowany jako admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /authentication/login.php');
    exit();
}

// Sprawdzenie, czy formularz został wysłany
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'admin'; // Ustalamy rolę na admin

    // Sprawdzenie, czy nazwa użytkownika już istnieje
    $stmt = $conn->prepare("SELECT * FROM bank.users WHERE username = ?");
    $stmt->bindParam(1, $username);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo json_encode(['status' => 'error', 'message' => 'Nazwa użytkownika już istnieje.']);
    } else {
        // Tworzenie nowego konta administratora
        $stmt = $conn->prepare("INSERT INTO bank.users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $password);
        $stmt->bindParam(3, $role);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Konto administratora zostało utworzone.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Błąd podczas tworzenia konta.']);
        }
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Utwórz konto administratora</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Dodaj jQuery -->
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
        <h1>Utwórz konto administratora</h1>
        <form id="createAdminForm">
            <input type="text" id="adminUsername" placeholder="Nazwa użytkownika" required>
            <input type="email" id="adminEmail" placeholder="E-mail" required>
            <input type="password" id="adminPassword" placeholder="Hasło" required>
            <button type="submit">Utwórz konto</button>
        </form>
        <div id="adminMessage" class="message"></div>
    </div>

    <script>
        $(document).ready(function() {
        $("#createAdminForm").submit(function(event) {
        event.preventDefault(); 

        var username = $("#adminUsername").val();
        var email = $("#adminEmail").val(); 
        var password = $("#adminPassword").val();

        $.ajax({
            url: '/transactions/api.php',
            type: 'POST',
            data: { action: 'create_admin', username: username, email: email, password: password }, 
            success: function(response) {
                var result = JSON.parse(response);
                $("#adminMessage").text(result.message);
            },
            error: function() {
                $("#adminMessage").text('Wystąpił błąd podczas tworzenia konta.');
            }
        });
    });
});

    </script>
</body>
</html>
