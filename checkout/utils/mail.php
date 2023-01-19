<?php

function sendCustomerMail($to, $subject, $message, $from)
{
    $headers = 'From: ' . $from . "\r\n";
    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}
