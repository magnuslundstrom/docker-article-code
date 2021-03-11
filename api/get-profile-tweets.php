<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';

session_start();

$loggedInUserId = $_SESSION['user']['user_id'] ?? null;
$userId = $_GET['userId'] ?? null;
$skip = $_GET['skip'] ?? 0;

if (!$loggedInUserId) {
    sendJSON(400, 'error', 'you must authenticated');
}

if (!ctype_digit($userId)) {
    sendJSON(400, 'error', 'invalid id');
}

try {
    $db = new Database();
    $sql = "
    SELECT
    u.user_id,
     CASE
 		WHEN l.tweet_fk IS NULL THEN 0
 		ELSE 1
     END AS likedStatus,
    CONCAT(first_name, ' ', last_name) as name,
    t.tweet_id,
    t.body,
    t.created_at as createdAt,
    num_likes as numLikes,
    num_comments as numComments
    FROM tweets as t
    JOIN users as u
    ON u.user_id = t.user_fk
    LEFT JOIN likes as l
    ON l.user_fk = :loggedInUserId AND l.tweet_fk = tweet_id
    WHERE u.user_id = :userId
    ORDER BY t.created_at DESC
    LIMIT :skip, 10"
    ;

    $db->prepare($sql);
    $db->statement->bindValue('loggedInUserId', $loggedInUserId);
    $db->statement->bindValue('userId', $userId);
    $db->statement->bindValue('skip', $skip, PDO::PARAM_INT);
    $db->statement->execute();
    $data = $db->getAll();

    header('Content-Type: application/json');
    echo json_encode($data);
} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}