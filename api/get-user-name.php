<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();

$userId = $_GET['userId'] ?? null;

if (!$userId || !ctype_digit($userId)) {
    sendJSONError(400, 'no or wrong userId provided');
}

try {
    $db = new Database();

    $sql = '
SELECT CONCAT(first_name, " ", last_name) as name
FROM users
WHERE user_id = :userId
LIMIT 1
    ';

    $name = $db->prepare($sql)->bindAndExecute(['userId', $userId])->getOne();
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($name);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}