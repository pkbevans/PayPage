<?php
include '../../checkout/utils/mail.php';

$from = "paypage@bondevans.com";
$to = "pkbevans@gmail.com";
$subject = "Hello Cocker";
$message = "This is an email from PayPage@bondevans.com";

if (sendCustomerMail($to, $subject, $message, $from)) {
    echo 'Success!';
} else {
    echo 'UNSUCCESSFUL...';
}
?>