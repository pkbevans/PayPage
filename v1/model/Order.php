<?php

class OrderException extends Exception {}

class Order {
    private $id;
    private $merchantReference;
    private $amount;
    private $refundAmount;
    private $currency;
    private $customerId;
    private $customerEmail;
    private $status;
    private $datetime;
public function __construct( $id, $merchantReference, $amount, $refundAmount, $currency, $customerId, $customerEmail, $status, $datetime){
    $this->id = $id;
    $this->merchantReference = $merchantReference;
    $this->amount = $amount;
    $this->refundAmount = $refundAmount;
    $this->currency = $currency;
    $this->customerId = $customerId;
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
        $Order['amount'] = $this->getAmount();
        $Order['refundAmount'] = $this->getRefundAmount();
        $Order['currency'] = $this->getCurrency();
        $Order['customerId'] = $this->getCustomerId();
        $Order['customerEmail'] = $this->getCustomerEmail();
        $Order['status'] = $this->getStatus();
        $Order['datetime'] = $this->getDatetime();
        return $Order;
    }
}