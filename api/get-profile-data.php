<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
$userId = $_GET['userId'] ?? null;

if (!ctype_digit($userId)) {
    sendJSON(400, 'error', 'invalid id');
}

try {
    $db = new Database();
    $sql = "SELECT
    u.user_id,
    CONCAT(first_name, ' ', last_name) as name,
    u.created_at as createdAt,
    u.following_count as followingCount,
    u.follower_count as followerCount,
    ud.description,
    CASE
    WHEN f.followee_fk IS NULL THEN 0
    ELSE 1
    END AS followStatus,
    u.tweet_count as tweetCount
    FROM users as u
    LEFT JOIN follows as f
    ON f.followee_fk = :loggedInUser AND follower_fk = u.user_id
    LEFT JOIN user_descriptions as ud
    ON ud.user_fk = u.user_id
    WHERE user_id = :id;";
    $user = $db->prepare($sql)->bindAndExecute(['id', $userId, 'loggedInUser', $loggedInUser])->getOne();
    $user['createdAt'] = date_format(new DateTime($user['createdAt']), "F Y");
    header('Content-type: application/json');
    echo json_encode($user);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}