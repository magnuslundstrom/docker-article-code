<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';

session_start();

$loggedInUser = $_SESSION['user']['user_id'];
if (!$loggedInUser) {
    sendJSONError(400, 'you must be authenticated');
}
try {
    $dbObj = new Database();
    $dbObj->db->beginTransaction();

    // DECREASE FOLLOWING_COUNT
    $sql = 'DELETE FROM follows WHERE follower_fk = :loggedInUser;';
    $dbObj->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser]);
    // DECREASE FOLLOWER_COUNT
    $sql = 'DELETE FROM follows WHERE followee_fk = :loggedInUser;';
    $dbObj->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser]);
    // DECREASE NUM_LIKES
    $sql = 'DELETE FROM likes WHERE user_fk = :loggedInUser;';
    $dbObj->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser]);
    // DECREASE NUM_COMMENTS
    $sql = 'DELETE FROM comments WHERE user_fk = :loggedInUser;';
    $dbObj->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser]);

    // DELETE USER
    $sql = '
    DELETE FROM users
    WHERE user_id = :loggedInUser';
    $dbObj->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser]);
    $dbObj->db->commit();

    sendJSON(200, 'message', 'successfully deleted');

} catch (PDOException $ex) {
    $dbObj->db->rollBack();
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}