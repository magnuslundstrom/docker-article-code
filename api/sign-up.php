<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

$firstName = $_POST['firstName'] ?? null;
$lastName = $_POST['lastName'] ?? null;
$password = $_POST['password'] ?? null;
$email = $_POST['email'] ?? null;

validator($firstName, 'firstName', 2, 50);
validator($lastName, 'lastName', 2, 50);
validator($password, 'password', 5, 25);
validator($email, 'email');

try {
    $connection = new Database();
    $sql = 'INSERT INTO USERS (first_name, last_name, password, email) VALUES(:firstName, :lastName, :password, :email)';
    $binds = [
        'firstName', $firstName,
        'lastName', $lastName,
        'password', password_hash($password, PASSWORD_DEFAULT),
        'email', $email,
    ];

    $id = $connection->prepare($sql)->bindAndExecute($binds)->getLastId();

    if (!$id) {
        http_response_code(400);
        echo 'WTF!';
        exit();
    }
    http_response_code(200);
    echo $id;

} catch (PDOException $ex) {
    if ($ex->errorInfo[1] == 1062) {
        sendJSON(400, 'email', 'Email already exists');
    }

    sendJSON(500, $ex);

}