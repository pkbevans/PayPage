
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
<body>
    <div id="container">
        <input type="text" id="aNumber" value="61">
        <button type="button" onclick="clicked()">Press Me</button>
    </div>
</body>
<script>
function doPromise(myNumber){
    return new Promise(function(resolve, reject) {
        if(myNumber=="61"){
            resolve ("RESOLVE");
        }else{
            reject("REJECT");
        }
    });
}
function clicked(){
    doPromise(document.getElementById("aNumber").value)
    .then(successMessage=>{
        console.log(successMessage);
    })
    .catch(errorMessage=>{
        console.log(errorMessage);
    });
}
</script>
</html>
