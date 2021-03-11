<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
if (!$loggedInUser) {

}

try {
    $db = new Database();
    $sql = '
    SELECT
    user_id,
    created_at as createdAt,
    email,
    CONCAT(first_name, " ", last_name) as name
    FROM users
    WHERE user_id NOT IN (SELECT follower_fk FROM follows WHERE followee_fk = :loggedInUser)
    AND user_id != :loggedInUser
    ORDER BY rand()
    LIMIT 4
    ';

    $users = $db->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser])->getAll();

    foreach ($users as &$user) {
        $user['followStatus'] = false;
    }

    header('Content-Type: application/json');
    echo json_encode($users);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}