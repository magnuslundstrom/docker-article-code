<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
$userId = $_GET['userId'] ?? null;
$skip = $_GET['skip'] ?? 0;

if (!$loggedInUser) {
    sendJSONError(400, 'you must authenticated to access');
}
if (!$userId || !ctype_digit($userId)) {
    sendJSONError(400, 'no or wrong userId provided');
}

try {
    $db = new Database();

    $sql = '
    SELECT
    u.user_id, CONCAT(first_name, " ", last_name) as name, ud.description,
    CASE
    WHEN exists(SELECT * FROM follows WHERE follower_fk = user_id AND followee_fk = :loggedInUser) != 0 THEN 1
    ELSE 0
    END AS follow_status
    FROM follows as f
    JOIN users as u
    ON u.user_id = f.follower_fk
    LEFT JOIN user_descriptions as ud
    ON f.follower_fk = ud.user_fk
    WHERE f.followee_fk = :userId
    ORDER BY f.created_at
    LIMIT :skip, 10
    ';

    $followers = $db->prepare($sql);
    $db->statement->bindValue('loggedInUser', $loggedInUser);
    $db->statement->bindValue('userId', $userId);
    $db->statement->bindValue('skip', $skip, PDO::PARAM_INT);
    $db->statement->execute();
    $followers = $db->statement->fetchAll();

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($followers);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}