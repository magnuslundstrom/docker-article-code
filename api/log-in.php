<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';

$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

validator($email, 'email');
validator($password, 'password', 2, 20);

try {
    $connection = new Database();
    $sql = 'SELECT
    user_id,
    CONCAT(first_name, " ", last_name) as name,
    email,
    password
    FROM users WHERE email = :email';

    $connection->prepare($sql);

    $user = $connection->bindAndExecute([':email', $email])->getOne();
    if ($user) {
        $verify = password_verify($password, $user['password']);
    }

    if (!$user || !$verify) {
        sendJSON(400, 'general', 'Email or password was wrong');
    }

    unset($user['password']);
    session_start();
    $_SESSION['user'] = $user;

    sendJSON(200, 'user', $user);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}