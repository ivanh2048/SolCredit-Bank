<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'SoloCredit';
$username = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASS') ?: '2004';

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Błąd połączenia z bazą danych: " . $e->getMessage());
    die("Wystąpił błąd. Proszę spróbować później.");
}

$mail_host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
$mail_username = getenv('MAIL_USERNAME') ?: 'ivan.hrabovskyi.uam@gmail.com';
$mail_password = getenv('MAIL_PASSWORD') ?: 'Obk@2022';
$mail_port = getenv('MAIL_PORT') ?: 587; // або 465 для SSL
$mail_from = getenv('MAIL_FROM') ?: 'solocredit@bank.com';
$mail_from_name = getenv('MAIL_FROM_NAME') ?: 'SoloCredit';
?>
