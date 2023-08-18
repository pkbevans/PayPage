<?php

$from = "paypage@bondevans.com";
$to = "pkbevans@gmail.com";
$subject = "Hello Cocker";
$message = "This is an email from PayPage@bondevans.com";

if (sendCustomerMail($to, $subject, $message, $from)) {
    echo 'Success!';
} else {
    echo 'UNSUCCESSFUL...';
}

function sendCustomerMail($to, $subject, $message, $from)
{
    $headers = 'From: ' . $from . "\r\n";
    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}
?>