Dear <?php echo $firstName. " " . $lastName;?>,

Welcome to PayPage.

Please verify your email by clicking on this link: https://<?=$_SERVER['HTTP_HOST']?>/payPage/common/v1/controller/users.php?id=<?php echo $lastUserID. "&verificationCode=". $verificationCode;?>

This email was auto-generated - please do not reply.
