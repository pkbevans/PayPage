<?php

class OrderException extends Exception {}

class Order {
    private $id;
    private $merchantReference;
    private $orderDetails;
    private $amount;
    private $refundAmount;
    private $currency;
    private $customerId;
    private $customerEmail;
    private $status;
    private $datetime;
    private $customerUserId;
public function __construct( $id, $merchantReference, $orderDetails, $amount, $refundAmount, $currency, $customerId, $customerUserId, $customerEmail, $status, $datetime){
    $this->id = $id;
    $this->merchantReference = $merchantReference;
    $this->orderDetails = $orderDetails;
    $this->amount = $amount;
    $this->refundAmount = $refundAmount;
    $this->currency = $currency;
    $this->customerId = $customerId;
    $this->customerUserId = $customerUserId;
    $this->customerEmail = $customerEmail;
    $this->status = $status;
    $this->datetime = $datetime;
   }

    public function getID(){
        return $this->id;
    }
    public function getMerchantReference(){
        return $this->merchantReference;
    }
    public function getOrderDetails(){
        return $this->orderDetails;
    }
    public function getAmount(){
        return $this->amount;
    }
    public function getRefundAmount(){
        return $this->refundAmount;
    }
    public function getCurrency(){
        return $this->currency;
    }
    public function getCustomerId(){
        return $this->customerId;
    }
    public function getCustomerUserId(){
        return $this->customerUserId;
    }
    public function getCustomerEmail(){
        return $this->customerEmail;
    }
    public function getStatus(){
        return $this->status;
    }
    public function getDatetime(){
        return $this->datetime;
    }
    public function setID($id){
        if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223379999 || $this->id !== null)){
            throw new OrderException("Order ID Error");
        }
        $this->id = $id;
    }
    public function setMerchantReference($merchantReference){
        if(strlen($merchantReference) < 0 || strlen($merchantReference)> 45){
            throw new OrderException("MRN Error");
        }
        $this->merchantReference = $merchantReference;
    }
    public function setOrderDetails($orderDetails){
        $result = json_decode($orderDetails);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new OrderException("OrderDetails error");
        }
        $this->orderDetails = $orderDetails;
    }
    public function setAmount($amount){
        if($amount <0){
            throw new OrderException("Amount error");
        }
        $this->amount = $amount;
    }
    public function setRefundAmount($refundAmount){
        if($refundAmount <0){
            throw new OrderException("Refund Amount error");
        }
        $this->refundAmount = $refundAmount;
    }
    public function setCurrency($currency){
        $this->currency = $currency;
    }
    public function setCustomerId($customerId){
        $this->customerId = $customerId;
    }
    public function setCustomerUserId($customerUserId){
        $this->customerUserId = $customerUserId;
    }
    public function setCustomerEmail($customerEmail){
        $this->customerEmail = $customerEmail;
    }
    public function setStatus($status){
        $this->status = $status;
    }
    public function setDatetime($datetime){
        $this->datetime = $datetime;
    }
    
    public function returnOrderAsArray(){
        $Order = array();
        $Order['id'] = $this->getID();
        $Order['merchantReference'] = $this->getMerchantReference();
        $Order['orderDetails'] = $this->getOrderDetails();
        $Order['amount'] = $this->getAmount();
        $Order['refundAmount'] = $this->getRefundAmount();
        $Order['currency'] = $this->getCurrency();
        $Order['customerId'] = $this->getCustomerId();
        $Order['customerUserId'] = $this->getCustomerUserId();
        $Order['customerEmail'] = $this->getCustomerEmail();
        $Order['status'] = $this->getStatus();
        $Order['datetime'] = $this->getDatetime();
        $Order['hash'] = $this->signOrderData();
        return $Order;
    }
    private function signOrderData(){
        $secretKey = '3c78b516c4d94f15b8a522fd0c22c471c776d53abd1e4feb91b24d1b10cd0545f063f4eb72e34d8ba06236c8eb221974ac8fb2ece8134feea0021d60f992205a379a5737bb6349b6b14bcc4557d4e8130b98569235d24202a5432941332af81bb3de2a7afb7c4d02b6bfb731a2610a6ed59a0b32a8e64a08b5a0c5b46a81915b';
        $data=$this->id.$this->merchantReference.$this->amount.$this->refundAmount.$this->currency.$this->customerId.$this->customerEmail.$this->status.$this->datetime.$this->customerUserId;
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }
}