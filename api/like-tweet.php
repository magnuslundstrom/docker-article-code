<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';
session_start();

$tweetToBeLiked = $_POST['tweetId'] ?? null;

$userId = $_SESSION['user']['user_id'] ?? null;

if (!$userId) {
    sendJSON(400, 'error', 'you must be authenticated');
}

if (!$tweetToBeLiked) {
    sendJSON(400, 'error', 'you must provide a tweet to like');
}

try {
    $bindArr = ['tweetId', $tweetToBeLiked, 'userFk', $userId];
    $db = new Database();
    $sql = 'SELECT * FROM likes WHERE tweet_fk = :tweetId AND user_fk = :userFk';
    $data = $db->prepare($sql)->bindAndExecute($bindArr)->rowCount();

    if (!$data) {
        $sql = '
    INSERT INTO likes
    (tweet_fk, user_fk)
    VALUES
    (:tweetId, :userFk)';
        $data = $db->prepare($sql)->bindAndExecute($bindArr)->rowCount();
        sendJSON(201, 'success', 'liked');
    } else {
        $sql = 'DELETE FROM likes WHERE user_fk = :userFk AND tweet_fk = :tweetId';
        $data = $db->prepare($sql)->bindAndExecute($bindArr)->rowCount();
        sendJSON(201, 'success', 'unliked');
    }
} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

};