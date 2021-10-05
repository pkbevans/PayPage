const expDate = document.getElementById("expiryDate");

function expiryDateValid() {
    d = new Date();
    todayYear = d.getFullYear();
    todayMonth = d.getMonth();
    xMonth = parseInt(expDate.value.substring(0,2));
    xYear = 2000 + parseInt(expDate.value.substring(3,5));
    if (xYear < todayYear || (xYear === todayYear && xMonth < todayMonth) || xMonth > 12 || xMonth < 1) {
        return false;
    }
    return true;
}

expDate.addEventListener('input',(event)=>{
    const val = event.target.value.toString();
    // If last char is invalid - ignore it
    myChar = val.charAt(val.length-1);
    if(myChar<'0' || myChar>'9'){
        // Ignore invalid characters
        if(val === "1/"){
            // If its a slash following 1 insert the leading zero
            event.target.value = "01/"
        }else {
            //Ignore
            event.target.value = val.substring(0, val.length - 1);
        }
        return;
    }
    if(val.length>5){
        //Ignore
        event.target.value = val.substring(0, val.length - 1);
    }
    switch (val.length) {
        case 0:
            break;
        case 1:
            if(val > 1){
                event.target.value = "0"+ val+"/";
            }
            break;
        case 2:
            if((val>12)){
                event.target.value = "12" +"/";
            }else if(val<1) {
                event.target.value = "01" + "/";
            }else{
                event.target.value = val + "/";
            }
            break;
        case 3:
    }

});
expDate.addEventListener('keydown',(event)=> {
    const val = event.target.value.toString();
    if(event.key === "Backspace"){
        if (event.target.selectionStart === 3 ){
            event.target.value = val.substring(0, 2);
        }else if(event.target.selectionStart === 4){
            event.target.value = val.substring(0, 3);
        }
    }
});
