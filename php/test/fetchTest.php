
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
<body>
    <div id="container">
        <input type="text" id="aNumber" value="EBDB9D3C634CFAB0E053AF598E0A65AC">
        <button type="button" onclick="clicked()">Press Me</button>
    </div>
</body>
<script>
function fetchCustomer(id){
    return fetch("/payPage/api/get_customer.php", {
      method: "post",
      body: JSON.stringify({
          "customerId" : id
      })
    })
    .then((result) => result.json())
    .then(res => {
        if(res.responseCode === 200){
            console.log(JSON.stringify(res, undefined, 2));
        }else{
            console.log("ERROR. Unable to retreive id="+id)
            console.log(res);
        }
    })
    .catch(error => {
        console.log("ERROR: "+error)
    })
}

function clicked(){
    fetchCustomer(document.getElementById("aNumber").value);
}
</script>
</html>
