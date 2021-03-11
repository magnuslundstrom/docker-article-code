<?php
require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$userId = $_SESSION['user']['user_id'] ?? null;
$tweetBody = $_POST['tweetBody'] ?? null;

if (!$userId) {
    sendJSON(401, 'error', 'you must be authenticated');
}
validator($tweetBody, 'tweetBody', 0, 140);

try {
    $db = new Database();
    $sql = 'INSERT INTO tweets (body, user_fk) VALUES (:body, :id)';
    $data = $db->prepare($sql)->bindAndExecute(['body', $tweetBody, 'id', $userId])->rowCount();
    $id = $db->getLastId();

    if (!$data) {
        sendJSONError(400, 'something went wrong and tweet was not created');
    }

    header('Content-Type: application/json');
    echo '{"id":"' . $id . '"}';

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}