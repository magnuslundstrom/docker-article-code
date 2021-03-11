<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$userId = $_SESSION['user']['user_id'] ?? null;
$tweetId = $_GET['tweetId'] ?? null;

if (!$tweetId) {
    sendJSONError(400, 'you must provide a tweetId');
} else if (!ctype_digit($tweetId)) {
    sendJSONError(400, 'you must provide a valid tweetId');
}

if (!$userId) {
    sendJSONError(400, 'you must be authenticated');
}

try {
    $db = new Database();
    $sql = '
    SELECT
    tweet_id,
    body,
    tweets.created_at as createdAt,
    user_id,
    num_likes AS numLikes,
    follower_count as followerCount,
    num_comments AS numComments,
    following_count as followingCount,
    user_id,
    CASE
    WHEN
    EXISTS(SELECT * FROM likes WHERE tweet_fk = :tweetId AND user_fk = :userId)
    THEN 1
    ELSE 0
    END AS likedStatus,
    CONCAT(first_name, " ", last_name) as name
    FROM
    tweets
    JOIN users
    ON user_fk = user_id
    WHERE tweet_id = :tweetId
    LIMIT 1;
    ';

    $tweet = $db->prepare($sql)->bindAndExecute(['tweetId', $tweetId, 'userId', $userId])->getOne();

    $sql = '
    SELECT
    comment_id,
    user_id as commenter_id,
    comment_body as body,
    c.created_at as createdAt,
    CONCAT(first_name, " ", last_name) as name,
    follower_count as followerCount,
    following_count as followingCount
    FROM comments as c
    JOIN users
    ON user_fk = user_id
    WHERE tweet_fk = :tweetId
    ORDER BY c.created_at DESC
    LIMIT 20';

    $comments = $db->prepare($sql)->bindAndExecute(['tweetId', $tweetId])->getAll();
    $tweet['comments'] = $comments;

    header('Content-Type: application/json');
    echo json_encode($tweet);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}