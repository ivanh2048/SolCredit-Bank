<?php
session_start();

$_SESSION['message'] = 'You have been successfully logged out.';

session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_start();
session_regenerate_id(true);

header('Location: login.php');
exit();
?>
