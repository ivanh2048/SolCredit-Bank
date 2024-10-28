<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

$username = $_SESSION['username'] ?? null;
$card_number = 'N/A';
$balance = '100.00';
$expiry_date = '12/25';

if ($username) {
    $sql = "SELECT * FROM bank.users WHERE username=:username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $card_number = $user['card_number'] ?? 'N/A';
        $balance = $user['balance'] ?? '100.00';
        $expiry_date = $user['expiry_date'] ?? '12/25';
    }
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solocredit Mobile Dashboard</title>
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

        .dashboard-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 900px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            padding: 30px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2); 
            border-radius: 20px; 
        }

        .balance-section {
            text-align: left;
            flex: 0.5; 
        }

        .balance-section h1 {
            margin: 0;
            font-size: 2.5rem;
            color: #2C3E50;
        }

        .balance-section p {
            margin: 5px 0;
            color: #7f8c8d;
            font-size: 1rem;
        }

        .card-container {
            width: 260px;
            height: 160px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 30px;
            margin-bottom: 20px;
            perspective: 1000px;
        }

        .bank-card {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.4s ease-in-out, box-shadow 0.3s ease-in-out;
            background: linear-gradient(135deg, #3b5998, #8b9dc3); 
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            justify-content: center; 
            align-items: center; 
        }

        .flipped {
            transform: rotateY(180deg);
        }

        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
        }

        .card-front {
            background: linear-gradient(135deg, #3b5998, #8b9dc3);
        }

        .card-back {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            transform: rotateY(180deg);
        }

        .card-number {
            font-size: 1.2em;
            letter-spacing: 2px;
        }

        .card-holder {
            font-size: 1rem;
        }

        .card-cvv {
            font-size: 1.2em;
            color: #e74c3c;
        }

        .mask-cvv {
            background-color: #ccc;
            color: transparent;
            border-radius: 5px;
            padding: 2px 6px;
        }

        .magnetic-strip {
            width: 100%;
            height: 30px;
            background: #000;
            margin-bottom: 10px;
        }

        .buttons-container {
            display: flex;
            flex-direction: column; 
            justify-content: center;
            gap: 10px; 
        }

        .buttons-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .buttons-section .button {
            transition: transform 0.2s ease-in-out, background-color 0.3s; /* Додаємо анімацію при наведенні */
        }

            .buttons-section .button:hover {
                transform: scale(1.1); 
                background-color: #f0f0f0; 
            }

        .button {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 60px;
            height: 60px;
            border: 2px solid #ccc; 
            border-radius: 50%; 
            transition: background-color 0.3s, transform 0.3s; 
            cursor: pointer; 
        }

        .button img {
            width: 28px;
            height: 28px;
        }

        .button:hover img {
            transform: rotate(15deg);
            transition: transform 0.3s ease;
        }

        .button:hover {
            background-color: #f0f0f0;
            transform: scale(1.1);
        }

        .card-container:hover .bank-card {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            transition: box-shadow 0.6s ease;
        }

        .icon {
            width: 30px;
            height: 30px;
            fill: #3498db; 
        }

        .button:hover .icon {
            fill: #2c3e50; 
        }

        .logout-button {
            background-color: #e74c3c; 
            border: none;
            font-size: 1rem;
            padding: 0.7rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .logout-button:hover {
            background-color: #c0392b;
        }

        .modal {
            display: none; 
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); 
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-header {
            text-align: left;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .modal-close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #e74c3c;
        }

        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            border-radius: 8px;
            background-color: #f7f7f7;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
        }

        .settings-item:hover {
            background-color: #e0e0e0;
        }

        .settings-item button {
            padding: 5px 15px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .settings-item button:hover {
            background-color: #2980b9;
        }

        .settings-item button:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="balance-section">
        <h1><?php echo htmlspecialchars($balance); ?> PLN</h1>
        <p>Własne środki: <?php echo htmlspecialchars($balance); ?> PLN</p>
        <p>Kredytowy limit: 20 000 PLN</p>
    </div>

    <div class="card-container">
        <div class="bank-card" id="bank-card">
            <div class="card-front">
                <div class="card-number">**** **** **** <?php echo substr($card_number, -4); ?></div>
                <div class="card-expiry"><?php echo htmlspecialchars($expiry_date); ?></div>
                <div class="card-holder"><?php echo htmlspecialchars($username ?? 'N/A'); ?></div>
            </div>

            <div class="card-back">
                <div class="magnetic-strip"></div>
                <div class="card-cvv">CVV: <span class="mask-cvv">***</span></div>
            </div>
        </div>
    </div>

    <div class="buttons-section">
        <a href="transfer.php" class="button">
            <img src="/image/money.png" class="icon" alt="Transfer Icon">
        </a>
        <a href="history.php" class="button">
            <img src="/image/history.png" class="icon" alt="History Icon">
        </a>
        <button class="button" id="settingsButton">
            <img src="/image/settings.png" class="icon" alt="Settings Icon"> 
        </button>
        <a href="logout.php" class="logout-button">
            <img src="/image/logout.png" class="icon" alt="Logout Icon">
        </a>
    </div>
</div>

<div id="settingsModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" id="closeModal">&times;</span>
        <div class="modal-header">Nastawienia karty</div>
        <div class="settings-item">
            <span>Zmień PIN</span>
            <button id="changePinButton" type="button">Zmień</button>
        </div>

        <div class="settings-item">
            <span>Zablokuj/odblokuj kartę</span>
            <button id="toggleCardLockButton" type="button">Zablokuj</button>
        </div>

        <div class="settings-item">
            <span>Limity transakcji (online/offline)</span>
            <button id="setTransactionLimitsButton" type="button">Ustaw</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#settingsButton").click(function() {
            $("#settingsModal").css("display", "block");
        });

        $("#closeModal").click(function() {
            $("#settingsModal").css("display", "none");
        });

        $(window).click(function(event) {
            if ($(event.target).is("#settingsModal")) {
                $("#settingsModal").css("display", "none");
            }
        });

        $("#changePinButton").click(function() {
            var newPin = prompt("Wprowadź nowy PIN (4 cyfry):");
            if (newPin && newPin.length === 4 && !isNaN(newPin)) {
                $.ajax({
                    url: 'change_pin.php',
                    type: 'POST',
                    data: { new_pin: newPin },
                    success: function(response) {
                        var result = JSON.parse(response);
                        alert(result.message);
                    },
                    error: function() {
                        alert('Wystąpił błąd podczas zmiany PIN.');
                    }
                });
            } else {
                alert("Nieprawidłowy PIN. Spróbuj ponownie.");
            }
        });

        $("#toggleCardLockButton").click(function() {
            var currentText = $(this).text();
            var lockStatus = (currentText === "Zablokuj") ? "lock" : "unlock";
            var button = $(this); 

            $.ajax({
                url: 'toggle_card_lock.php',
                type: 'POST',
                data: { lock_status: lockStatus },
                success: function(response) {
                    var result = JSON.parse(response);
                    alert(result.message);
                    button.text((lockStatus === "lock") ? "Odblokuj" : "Zablokuj");
                },
                error: function() {
                    alert('Wystąpił błąd podczas zmiany statusu karty.');
                }
            });
        });

        $("#setTransactionLimitsButton").click(function() {
            var newLimit = prompt("Wprowadź nowy limit transakcji (PLN):");
            if (newLimit && !isNaN(newLimit)) {
                $.ajax({
                    url: 'set_transaction_limit.php',
                    type: 'POST',
                    data: { new_limit: newLimit },
                    success: function(response) {
                        var result = JSON.parse(response);
                        alert(result.message);
                    },
                    error: function() {
                        alert('Wystąpił błąd podczas ustawiania limitu.');
                    }
                });
            } else {
                alert("Nieprawidłowy limit. Spróbuj ponownie.");
            }
        });
    });
</script>

</body>
</html>