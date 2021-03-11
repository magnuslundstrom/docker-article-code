<?php

require_once __DIR__ . '/private/db.php';
require_once __DIR__ . '/helperFunctions/sendRes.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;

try {
    $db = new Database();
    $sql = "
    SELECT
    search_term
    FROM
    recent_search
    WHERE user_search_fk = :loggedInUser
    GROUP BY search_term
    ORDER BY created_at DESC
    LIMIT 5
    ";
    $data = $db->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser])->getAll();

    http_response_code(200);
    header('Content-type: application/json');
    echo json_encode($data);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}