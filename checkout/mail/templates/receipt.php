Dear <?php echo $incoming->order->bill_to->firstName. " " . $incoming->order->bill_to->lastName;?>,

Thank you for ordering from us.

Your order number is <?php echo $incoming->order->referenceNumber;?>.

Here are your order details:

Pants
-----
Total amount: <?php echo "£".$incoming->order->amount?>

Thank you for your order.
This email was auto-generated - please do not reply.
