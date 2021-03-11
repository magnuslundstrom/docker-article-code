<?php
require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'] ?? null;
$commentBody = $_POST['commentBody'] ?? null;
$tweetFk = $_POST['tweetId'] ?? null;

if (!$loggedInUser || !$commentBody || !$tweetFk) {
    sendJSONError(400, 'you did not provide what you must');
}

try {
    $db = new Database();
    $sql = '
    INSERT INTO
    comments
    (user_fk, comment_body, tweet_fk)
    VALUES
    (:loggedInUser, :comment_body, :tweet_fk)';
    $id = $db->prepare($sql)->bindAndExecute(['loggedInUser', $loggedInUser, 'comment_body', $commentBody, 'tweet_fk', $tweetFk])->getLastId();
    if (!$id) {
        sendJSONError(400, 'something went wrong');
    }
    http_response_code(200);
    header('Content-type: application/json');
    echo '{"id":"' . $id . '"}';

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}