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

/*
The complexity arrises from beening able to access a conversation via either a reconized convId OR by passing in userId-userId
So what we are looking for is either a conversation that has these two userIds or simply the convId that was passed.
 */

try {
    $conversationDetails = ['conversationId' => 0, 'type' => 0, 'messages' => [], 'users' => []];
    $db = new Database();
    if (strpos($conversationId, '-')) {
        $ids = explode("-", $conversationId);
        foreach ($ids as &$id) {
            if (!ctype_digit($id)) {
                sendJSONError(400, 'invalid id');
            }
            (int) $id;
        }
        $sql = '
            SELECT c.conversation_id, c.type FROM users_in_conversations as uic
            JOIN conversations as c
            ON uic.conversation_fk = c.conversation_id
            WHERE uic.user_fk IN (:idOne, :idTwo) AND type = 0
            GROUP BY conversation_fk
            HAVING count(*) = 2';
        $conversation = $db->prepare($sql);
        $db->statement->bindValue('idOne', $ids[0]);
        $db->statement->bindValue('idTwo', $ids[1]);
        $db->statement->execute();
        $conversation = $db->getOne();

        if (!$conversation) {
            sendJSONError(400, 'no conversation found');
        } else {
            $conversationDetails['conversationId'] = $conversation['conversation_id'];
            $conversationDetails['type'] = $conversation['type'];

            $sql = '
           SELECT message_id, body, user_id, messages.created_at, seen, CONCAT(first_name, " ", last_name) as name FROM messages
           JOIN users on sender_fk = user_id
           WHERE conversation_fk = :conversationId
           ORDER BY messages.created_at DESC
           LIMIT 20
            ';
            $messages = $db->prepare($sql)->bindAndExecute(['conversationId', $conversation['conversation_id']])->getAll();
            $conversationDetails['messages'] = $messages;

        }

    } else {
        $sql = 'SELECT conversation_id, type FROM conversations WHERE conversation_id = :conversationId';
        $conversation = $db->prepare($sql)->bindAndExecute(['conversationId', $conversationId])->getOne();
        if (!$conversation) {
            sendJSONError(400, 'no conversation found');
        }

        $conversationDetails['conversationId'] = $conversation['conversation_id'];
        $conversationDetails['type'] = $conversation['type'];

        $sql = '
           SELECT message_id, body, user_id, messages.created_at, seen, CONCAT(first_name, " ", last_name) as name FROM messages
           JOIN users on sender_fk = user_id
           WHERE conversation_fk = :conversationId
           ORDER BY messages.created_at DESC
           LIMIT 20
            ';

        $messages = $db->prepare($sql)->bindAndExecute(['conversationId', $conversationId])->getAll();
        $conversationDetails['messages'] = $messages;
    }

    $sql = '
    SELECT CONCAT(first_name, " ", last_name) as name, user_id FROM users_in_conversations as uic
    JOIN users ON user_fk = user_id
    WHERE conversation_fk = :convId;
    ';
    $users = $db->prepare($sql)->bindAndExecute(['convId', $conversationDetails['conversationId']])->getAll();
    foreach ($users as $user) {
        if ($user['user_id'] != $loggedInUser) {
            array_push($conversationDetails['users'], $user);
        }
    }

    sendJSON(200, 'conversation', $conversationDetails);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}