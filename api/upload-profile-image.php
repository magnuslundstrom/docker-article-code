<?php
require_once __DIR__ . '/helperFunctions/sendRes.php';
require_once __DIR__ . '/helperFunctions/validator.php';

session_start();
$loggedInUser = $_SESSION['user']['user_id'];
if (!$loggedInUser) {
    sendJSONError(400, 'you must be authenticated');
}

$file = $_FILES['file'];

$fileName = $_FILES['file']['name'];
$fileTmpName = $_FILES['file']['tmp_name'];
$fileSize = $_FILES['file']['size'];
$fileError = $_FILES['file']['error'];
$filetype = $_FILES['file']['type'];

$fileExt = explode('.', $fileName);
$fileActualExt = strtolower(end($fileExt));

$allowed = ['jpg', 'jpeg', 'png'];

if (in_array($fileActualExt, $allowed)) {
    if ($fileError === 0) {
        if ($fileSize < 5000000) {
            $fileNameNew = 'profile-image-' . $loggedInUser . ".jpg";
            $fileDestination = __DIR__ . '/../images/profile-images/' . $fileNameNew;
            move_uploaded_file($fileTmpName, $fileDestination);

        } else {
            http_response_code(400);
            echo 'your file is too big';
        }
    } else {
        http_response_code(400);
        echo 'something went wrong';
    }
} else {
    http_response_code(400);
    echo 'you cant upload files of this type!';
}