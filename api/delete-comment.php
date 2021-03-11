<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
$commentId = $_GET['commentId'] ?? null;

if (!$loggedInUser) {
    sendJSONError(400, 'you must be authenticated');
}
if (!$commentId) {
    sendJSONError(400, 'you must provide a comment id');
}

try {

    $sql = '
    DELETE FROM comments
    WHERE comment_id = :commentId';

    $db = new Database();

    $data = $db->prepare($sql)->bindAndExecute(['commentId', $commentId])->rowCount();

    if (!$data) {
        sendJSONError(400, 'no comment with the provided id');
    }

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}