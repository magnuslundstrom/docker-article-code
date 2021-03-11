<?php

require_once __DIR__ . '/sendRes.php';

function validator($val, $name, $min = 0, $max = 100)
{
    if (!$val) {
        sendJSON(400, "$name", "You must provide a $name");
    } else if ($name === 'email') {
        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
            sendJSON(400, "$name", 'You must provide a valid email');
        }
    } else if (strlen($val) < $min) {
        sendJSON(400, "$name", "$name must be atleast $min characters");
    } else if (strlen($val) > $max) {
        sendJSON(400, "$name", "$name must be maximum $max characters");
    }
}