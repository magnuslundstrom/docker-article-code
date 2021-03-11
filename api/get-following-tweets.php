<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$userId = $_SESSION['user']['user_id'] ?? null;
$skip = $_GET['skip'] ?? 0;

try {
    $db = new Database();
    $sql = '
        SELECT
        t.created_at as createdAt,
        t.tweet_id,
        u.user_id,
        t.body,
        t.num_likes as numLikes,
        t.num_comments as numComments,
        CONCAT(first_name, " ", last_name) as name,
        CASE
        WHEN l.tweet_fk IS NULL THEN 0
        ELSE 1
        END AS likedStatus
        FROM follows AS f
        JOIN tweets AS t
        ON t.user_fk = f.follower_fk
        JOIN users AS u
        ON u.user_id = f.follower_fk
        LEFT JOIN likes as l
        ON l.user_fk = :id AND l.tweet_fk = t.tweet_id
        WHERE f.followee_fk = :id
        LIMIT :skip, 10
    ';

    $db->prepare($sql);
    $db->statement->bindValue('id', $userId);
    $db->statement->bindValue('skip', $skip, PDO::PARAM_INT);
    $db->statement->execute();
    $tweets = $db->getAll();

    sendJSON(200, 'tweets', $tweets);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}