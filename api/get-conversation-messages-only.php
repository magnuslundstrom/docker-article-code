<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
if (!$loggedInUser) {
    sendJSONError(400, 'you must be authenticated');
}
$conversationId = $_GET['conversationId'] ?? null;
if (!$conversationId) {
    sendJSONError(400, 'you must provide conversation id');
}

if (!ctype_digit($conversationId)) {
    sendJSONError(400, 'you must provide a valid conversation id');
}

try {

    $db = new Database();
    $sql = '
    SELECT message_id,body,sender_fk as user_id, created_at FROM messages
    WHERE conversation_fk = :convId
    ORDER BY created_at DESC
    LIMIT 20;';
    $messages = $db->prepare($sql)->bindAndExecute(['convId', $conversationId])->getAll();
    sendJSON(200, 'messages', $messages);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}