<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
if (!$loggedInUser) {
    sendJSONError(400, 'you must be authenticated');
}

try {
    $db = new Database();
    $sql = 'SELECT
    c.conversation_id,
    message_id,
    body,
    sender_fk as sender_id,
    seen,
    c.created_at as conversation_start,
    m.created_at as message_sent,
    c.type
    FROM users_in_conversations as uic
    JOIN conversations as c
    ON uic.conversation_fk = c.conversation_id
    LEFT JOIN messages as m
    ON c.latest_message_fk = m.message_id
    WHERE uic.user_fk = :loggedInUser
    GROUP BY uic.conversation_fk
    ORDER BY m.created_at DESC
    LIMIT 20
    ';

    $conversations = $db->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser])->getAll();
    if (!$conversations) {
        header('Content-type: application/json');
        echo '[]';
        exit();
    }

    $sql = '
        SELECT
        CONCAT(first_name, " ", last_name) as name,
        user_id,
        follower_count,
        following_count,
        u.created_at as user_joined
        FROM users_in_conversations as uic
        JOIN users as u
        ON u.user_id = uic.user_fk
        WHERE conversation_fk = :convId AND user_id != :loggedInUser;
        ';
    $q = $db->db->prepare($sql);

    foreach ($conversations as &$conversation) {
        $q->bindParam(':loggedInUser', $loggedInUser);
        $q->bindParam(':convId', $conversation['conversation_id']);
        $q->execute();
        $users = $q->fetchAll();
        $conversation['users'] = $users;
    }

    header('Content-Type: application/json');
    echo json_encode($conversations);
} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}