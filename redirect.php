<!DOCTYPE html>
<html>
<head>
</head>
<body>
<script type="text/javascript">
    var transactionId = "<?php echo $_POST['TransactionId']; ?>";
    function closeMe() {
        parent.hideStepUpScreen(transactionId);
    }
    closeMe();
</script>
</body>
</html>