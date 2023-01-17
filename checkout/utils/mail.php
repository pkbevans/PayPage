<?php

function sendCustomerMail($to, $subject, $message, $headers)
{
    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}
