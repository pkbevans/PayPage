<?php
include '../../checkout/utils/mail.php';

$template=



$headers = "From: no-reply@paypage.com\r\n";
$to = "pkbevans@gmail.com";
$subject = "Hello Cocker";
$message = "This is an email from PayPage";

if (sendCustomerMail($to, $subject, $message, $headers)) {
    echo 'Success!';
} else {
    echo 'UNSUCCESSFUL...';
}
?>