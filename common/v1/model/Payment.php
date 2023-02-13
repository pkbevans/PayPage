<?php

class PaymentException extends Exception {}
class Payment {
    private $id;
    private $orderId;
    private $type;
    private $amount;
    private $currency;
    private $cardNumber;
    private $cardType;
    private $authCode;
    private $gatewayRequestId;
    private $status;
    private $captured;
    private $datetime;
    
public function __construct( $id, $orderId, $type, $amount, $currency, $cardNumber, $cardType, $authCode, $gatewayRequestId, $status, $captured, $datetime){
        $this->setID($id); 
        $this->setOrderId($orderId); 
        $this->setType($type); 
        $this->setAmount($amount); 
        $this->setCurrency($currency); 
        $this->setCardNumber($cardNumber); 
        $this->setCardType($cardType); 
        $this->setAuthCode($authCode); 
        $this->setGatewayRequestId($gatewayRequestId);
        $this->setStatus($status); 
        $this->setCaptured($captured); 
        $this->datetime = $datetime; 
    }
//    $id, $orderId, $type, $amount, $currency, $cardNumber, $cardType, $authCode, $gatewayRequestId, $status, $captured, $datetime
    public function getID(){
        return $this->id;
    }
    public function getOrderId(){
        return $this->orderId;
    }
    public function getType(){
        return $this->type;
    }
    public function getAmount(){
        return $this->amount;
    }
    public function getCurrency(){
        return $this->currency;
    }
    public function getCardNumber(){
        return $this->cardNumber;
    }
    public function getCardType(){
        return $this->cardType;
    }
    public function getAuthCode(){
        return $this->authCode;
    }
    public function getGatewayRequestId(){
        return $this->gatewayRequestId;
    }
    public function getStatus(){
        return $this->status;
    }
    public function getCaptured(){
        return $this->captured;
    }
    public function getDatetime(){
        return $this->datetime;
    }
    public function setID($id){
        if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223379999 || $this->id !== null)){
            throw new PaymentException("Payment ID Error");
        }
        $this->id = $id;
    }
    public function setOrderId($orderId){
        if(strlen($orderId) < 0 || strlen($orderId)> 45){
            throw new PaymentException("OrderId` Error");
        }
        $this->orderId = $orderId;
    }
    public function setAmount($amount){
        if($amount <0){
            throw new PaymentException("Amount error");
        }
        $this->amount = $amount;
    }
    public function setType($type){
        if(strcmp($type, "PAYMENT") && strcmp($type, "VOID") && 
                    strcmp($type, "REFUND") && strcmp($type, "REVERSAL") && strcmp($type, "CAPTURE")){
            throw new PaymentException("Type error: ". $type);
        }
        $this->type = $type;
    }
    public function setCurrency($currency){
        $this->currency = $currency;
    }
    public function setCardNumber($cardNumber){
        $this->cardNumber = $cardNumber;
    }
    public function setCardType($cardType){
        $this->cardType = $cardType;
    }
    public function setGatewayRequestId($gatewayRequestId){
        $this->gatewayRequestId = $gatewayRequestId;
    }
    public function setAuthCode($authCode){
        $this->authCode = $authCode;
    }
    public function setStatus($status){
        $this->status = $status;
    }
    public function setCaptured($captured){
        $this->captured = $captured;
    }
    public function setDatetime($datetime){
        $this->datetime = $datetime;
    }
    
    public function returnPaymentAsArray(){
        $Payment = array();
        $Payment['id'] = $this->getId();
        $Payment['orderId'] = $this->getOrderId(); 
        $Payment['type'] = $this->getType(); 
        $Payment['amount'] = $this->getAmount(); 
        $Payment['currency'] = $this->getCurrency(); 
        $Payment['cardNumber'] = $this->getCardNumber();
        $Payment['cardType'] = $this->getCardType(); 
        $Payment['authCode'] = $this->getAuthCode(); 
        $Payment['gatewayRequestId'] = $this->getGatewayRequestId();
        $Payment['status'] = $this->getStatus(); 
        $Payment['captured'] = $this->getCaptured(); 
        $Payment['datetime'] = $this->getDatetime(); 
        return $Payment;
    }
}