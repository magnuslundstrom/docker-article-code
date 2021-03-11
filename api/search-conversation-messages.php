<?php
require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$loggedInUserId = $_SESSION['user']['user_id'] ?? null;
$conversationId = $_GET['convId'] ?? null;
$searchTerm = $_GET['searchTerm'] ?? null;

if (!$conversationId) {
    sendJSONError(400, 'you must provide a convId');
}

if (!ctype_digit($conversationId)) {
    sendJSONError(400, 'you must provide a valid convId');
}

if (strlen($searchTerm) == 0) {
    sendJSONError(400, 'search term must be atleast 1 character');
}

if (strlen($searchTerm) > 20) {
    sendJSONError(400, 'search term must be at maximum 20 characters');
}

try {

    $db = new Database();

    $sql = '
    SELECT
    message_id, body, sender_fk,messages.created_at,CONCAT(first_name, " ", last_name) as name, user_id
    FROM messages
    JOIN users ON sender_fk = user_id
    WHERE MATCH(body) Against(:term IN BOOLEAN MODE) AND conversation_fk = :convId
    ORDER BY messages.created_at DESC
    LIMIT 20';
    $messages = $db->prepare($sql)->bindAndExecute(['term', "$searchTerm*", 'convId', $conversationId])->getAll();

    http_response_code(200);
    header('Content-Type: application/json');
    echo JSON_ENCODE($messages);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}