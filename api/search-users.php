<?php
require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

$search = $_POST['search'] ?? '';
validator($search, 'search', 1, 140);

try {
    $db = new Database();
    $sql = '
    SELECT
    user_id,
    CONCAT(first_name, " ", last_name) as name
    FROM users
    WHERE CONCAT(first_name, " ", last_name)
    LIKE :search
    ORDER BY follower_count DESC';
    $data = $db->prepare($sql)->bindAndExecute(['search', "$search%"])->getAll();
    header('Content-type: application/json');
    echo json_encode($data);

} catch (PDOException $ex) {
    sendJSON(500, 'general', 'Please contact admin' . __LINE__);

}