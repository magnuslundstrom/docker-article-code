<?php
require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';
session_start();

$loggedInUser = $_SESSION['user']['user_id'] ?? null;
$conversationFk = $_POST['conversation_fk'] ?? null;
$body = $_POST['body'] ?? null;

if (!$loggedInUser) {
    sendJSON(400, 'message', 'you must be authenticated');
}

if (!$conversationFk) {
    sendJSON(400, 'message', 'you must provide a conversationId');
}

if (!$body) {
    sendJSON(400, 'message', 'you must provide a body');
}

// THIS MUST NEVER HAPPEN WITHOUT A PROPER CONV ID

try {

    $db = new Database();
    $sql = '
    INSERT INTO messages
    (body, sender_fk, conversation_fk)
    VALUES
    (:body, :loggedInUser, :conversation)';
    $id = $db->prepare($sql)->bindAndExecute(['body', $body, 'loggedInUser', $loggedInUser, 'conversation', $conversationFk])->getLastId();
    if (!$id) {
        sendJSONError(400, 'something went wrong');
    }
    http_response_code(200);
    header('content-type: application/json');
    echo json_encode(['id' => $id]);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}