<?php

require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';
require_once __DIR__ . '/private/db.php';

print_r($_ENV);

$db = new Database();
$sql = 'SELECT * FROM likes ';
$data = $db->prepare($sql)->bindAndExecute($bindArr)->getAll();
print_r($data);