<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
$receiver_fk = $_POST['receiverId'] ?? null;

if (!$loggedInUser) {
    sendJSONError(400, 'you must be authenticated');
}

if (!$receiver_fk || !ctype_digit($receiver_fk)) {
    sendJSONError(400, 'you must provide a valid receiverId');
}

/*
Still needs some work
If the conversation exists between the two users send back that Id
Else create a new conv and insert the users
 */

try {
    $db = new Database();
    $sql = '
            SELECT c.conversation_id, c.type FROM users_in_conversations as uic
            JOIN conversations as c
            ON uic.conversation_fk = c.conversation_id
            WHERE uic.user_fk IN (:idOne, :idTwo) AND type = 0
            GROUP BY conversation_fk
            HAVING count(*) = 2';
    $db->prepare($sql);
    $db->statement->bindValue('idOne', $loggedInUser);
    $db->statement->bindValue('idTwo', $receiver_fk);
    $db->statement->execute();
    $conversation = $db->getOne();

    if (isset($conversation['conversation_id'])) {
        sendJSON(200, 'conversation_id', $conversation['conversation_id']);
    }

    $db->db->beginTransaction();
    $sql = '
        INSERT INTO
        conversations
        (type)
        VALUES
        (0)';
    $conversationId = $db->prepare($sql)->bindAndExecute()->getLastId();

    $sql = '
    INSERT INTO users_in_conversations
    (conversation_fk, user_fk)
    VALUES
    (:conversationId, :userId)
    ';

    $db->prepare($sql)->bindAndExecute(['conversationId', $conversationId, 'userId', $loggedInUser]);
    $db->prepare($sql)->bindAndExecute(['conversationId', $conversationId, 'userId', $receiver_fk]);
    $db->db->commit();

    sendJSON(200, 'conversation_id', $conversationId);

} catch (PDOException $ex) {

    $db->db->rollBack();
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}