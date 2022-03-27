function newCard(flexDetails){
    // New card details received from newCard2.js
    billTo = {
        firstName: document.getElementById("bill_to_firstName").value,
        lastName: document.getElementById("bill_to_lastName").value,
        address1: document.getElementById("bill_to_address1").value,
        address2: document.getElementById("bill_to_address2").value,
        locality: document.getElementById("bill_to_locality").value,
        postalCode: document.getElementById("bill_to_postalCode").value,
        country: document.getElementById("bill_to_country").value
    };
    // TODO - New card/billing details but not to be stored
    console.log("newCard");
}
function validateForm(form){
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return false;
    }
    return true;
}
function addCard(){
    console.log("add card");
    document.getElementById("addCardSection").style.display = "block";
}
function usePaymentInstrument(){
    // Work out which card is selected
    var radios = document.getElementsByName('paymentInstrument');
    id="";
    for (var i = 0, length = radios.length; i < length; i++) {
      if (radios[i].checked) {
        // do whatever you want with the checked radio
        id = radios[i].value;
        break;
      }
    }
    if(id === "NEW"){
       addNewCard();
    }else{
        xxx = window['paymentInstrument_'+id];
        parent.onPaymentInstrumentUpdated(id, JSON.parse(xxx));
    }
}
function addNewCard(){
    form = document.getElementById('billingForm');
    if(validateForm(form)){
        getToken(newCard);
    }
}
