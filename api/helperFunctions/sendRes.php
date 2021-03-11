<?php


function sendJSONError($http, $message)
{
    http_response_code($http);
    header('Content-Type: application/json');
    $message = [
        "message" => $message
    ];
    echo json_encode($message);
    exit();
}

function sendJSON($http, $tag, $message)
{
    http_response_code($http);
    header('Content-type: application/json');
    $message = [
        $tag => $message
    ];
    echo json_encode($message);
    exit();
}
