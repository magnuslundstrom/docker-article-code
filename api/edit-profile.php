<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';
session_start();

$userId = $_SESSION['user']['user_id'] ?? null;

if (!$userId) {
    sendJSON(400, 'error', 'you must be authenticated');
}

$email = $_POST['email'] ?? null;
$firstName = $_POST['firstName'] ?? null;
$lastName = $_POST['lastName'] ?? null;
$description = $_POST['description'] ?? null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSONError(400, 'no or invalid email');
}

if (!$firstName || strlen($firstName) < 2) {
    sendJSONError(400, 'firstName must be atleast 2 chars');
}
if (!$lastName || strlen($lastName) < 2) {
    sendJSONError(400, 'lastName must be atleast 2 chars');
}

$oldEmail = $_SESSION['user']['email'];
$dbObj = new Database();

// First updates the properties sitting on the user entity and after updating/inserting/deleting user description

try {

    $sql = "
    SELECT
    email, user_id, description
    FROM users
    LEFT JOIN user_descriptions
    ON user_id = user_fk
    WHERE user_id = :userId;
    ";
    $user = $dbObj->prepare($sql)->bindAndExecute(['userId', $userId])->getOne();

    $dbObj->db->beginTransaction();

    $sql = '
    UPDATE users
    SET
    email = :email,
    first_name = :firstName,
    last_name = :lastName
    WHERE user_id = :userId
    ';

    $statement = $dbObj->db->prepare($sql);
    $statement->bindValue('email', $email);
    $statement->bindValue('firstName', $firstName);
    $statement->bindValue('lastName', $lastName);
    $statement->bindValue('userId', $userId, PDO::PARAM_INT);

    $statement->execute();

    // handle description part
    if ($user['description'] && !$description) {
        $sql = '
        DELETE FROM user_descriptions
        WHERE user_fk = :userId
        ';
        $data = $dbObj->prepare($sql)->bindAndExecute(['userId', $user['user_id']])->rowCount();
        if (!$data) {
            sendJSONError(400, 'something went wrong (delete description update)');
        } else {
            $dbObj->db->commit();
        }

    } else if (!$user['description'] && strlen($description) > 0) {
        $sql = '
        INSERT INTO user_descriptions
        (user_fk, description)
        VALUES
        (:userId, :description)
        ';
        $data = $dbObj->prepare($sql)->bindAndExecute(['userId', $user['user_id'], 'description', $description])->rowCount();
        if (!$data) {
            sendJSONError(400, 'something went wrong (insert description update)');
        } else {
            $dbObj->db->commit();
        }

    } else if ($user['description'] && $description !== $user['description']) {
        $sql = '
        UPDATE user_descriptions
        SET description = :description
        WHERE user_fk = :userId
        ';
        $data = $dbObj->prepare($sql)->bindAndExecute(['userId', $user['user_id'], 'description', $description])->rowCount();
        if (!$data) {
            sendJSONError(400, 'something went wrong (update description)');
        } else {
            $dbObj->db->commit();
        }
    } else {
        $dbObj->db->commit();
    }

    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['name'] = $firstName . ' ' . $lastName;

} catch (PDOException $ex) {
    if ($ex->errorInfo[1] == 1062) {
        $dbObj->db->rollBack();
        sendJSON(400, 'email', 'Email already exists');
    }
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

};