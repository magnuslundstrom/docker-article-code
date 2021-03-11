<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';
session_start();
$toBeFollowedId = $_POST['userToFollowId'] ?? null;

$userId = $_SESSION['user']['user_id'] ?? null;

if (!$userId) {
    sendJSON(400, 'error', 'you must be authenticated');
}

if (!$toBeFollowedId) {
    sendJSON(400, 'error', 'you must provide a user to follow');
}

if ($userId === $toBeFollowedId) {
    sendJSONError(400, 'you cant follow yourself!');
}

try {
    $bindArr = ['follower_fk', $toBeFollowedId, 'followee_fk', $userId];
    $db = new Database();
    $sql = 'SELECT * FROM follows WHERE follower_fk = :follower_fk AND followee_fk = :followee_fk';
    $data = $db->prepare($sql)->bindAndExecute($bindArr)->rowCount();
    if (!$data) {
        $sql = 'INSERT INTO follows (follower_fk, followee_fk) VALUES(:follower_fk, :followee_fk)';
        $data = $db->prepare($sql)->bindAndExecute($bindArr)->rowCount();
        sendJSON(200, 'newStatus', 'followed');

    } else {
        $sql = 'DELETE FROM follows WHERE follower_fk = :follower_fk AND followee_fk = :followee_fk';
        $data = $db->prepare($sql)->bindAndExecute($bindArr)->rowCount();
        sendJSON(200, 'newStatus', 'unfollowed');
    }
} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

};