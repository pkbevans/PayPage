function authenticate(path){
    return new Promise(function(resolve, reject) {
        // Check whether we already have an open session
        // Either: 
        // 1. No cookie at all - Show login screen
        // 2. Got cookie. Refresh Token expired - Show login screen
        // 3. Got cookie. Refresh token not expired but access token expired - refresh access token, then show search form
        // 4. Got cookie. access token not expired - Show search form
        let accessTokenExpires = getCookie("accessTokenExpires");
        if(accessTokenExpires){
            accessToken = getCookie("accessToken");
            var now = new Date();
            var d1 = Date.parse(accessTokenExpires);
            var d2 = Date.parse(now);
            if (d1 > d2) {
                console.log("authenticate: accessToken Still valid");
                // Access token still valid - No log in
                document.getElementById("loginSection").style.display="none"
                document.getElementById("contentSection").style.display="block"
                resolve(accessToken);
            }else{
                let refreshTokenExpires = getCookie("refreshTokenExpires");
                d1 = Date.parse(refreshTokenExpires);
                if (d1 > d2+60000) {    // Refresh if there is < 1 minute left
                    console.log("authenticate: refresh Access Token");
                    // Refresh token still valid - Refresh access token
                    refreshToken = getCookie("refreshToken");
                    sessionId = getCookie("sessionId");
                    console.log("accessToken just b4 refresh:"+accessToken);
                    refreshAccessToken(sessionId, accessToken,refreshToken)
                    .then((result)=>{
                        console.log(result);
                        var t = new Date();
                        t.setSeconds(t.getSeconds() + result.data.accessTokenExpiresIn);
                        document.cookie = "accessTokenExpires=" + t+';expires=;path='+path;
                        t = new Date();
                        t.setSeconds(t.getSeconds() + result.data.refreshTokenExpiresIn);
                        document.cookie = "refreshTokenExpires=" + t+';expires=;path='+path;
                        document.cookie = "sessionId=" + result.data.sessionId+';expires=;path='+path;
                        newAccessToken = result.data.accessToken;
                        newRefreshToken = result.data.refreshToken;
                        document.cookie = "accessToken=" + newAccessToken+';expires=;path='+path;
                        document.cookie = "refreshToken=" + newRefreshToken+';expires=;path='+path;
                        document.getElementById("loginSection").style.display="none"
                        document.getElementById("contentSection").style.display="block"
                        console.log("authenticate: New Access Token:"+newAccessToken+":"+newRefreshToken);
                        resolve(newAccessToken);
                    })
                    .catch(error => {
                        console.log("authenticate ERROR: ");
                        console.log(error);
                        // Show login screen
                        document.getElementById("loginSection").style.display="block"
                        document.getElementById("contentSection").style.display="none"
                        reject("LOGIN REQUIRED");
                    });;
                }else{
                    console.log("authenticate: show login screen1");
                    // Else show log in 
                    document.getElementById("loginSection").style.display="block"
                    document.getElementById("contentSection").style.display="none"
                    reject("LOGIN REQUIRED");
                }
            }
        }else{
            // Else show log in 
            console.log("authenticate: show login screen2");
            document.getElementById("loginSection").style.display="block"
            document.getElementById("contentSection").style.display="none"
            reject("LOGIN REQUIRED");
        }
    });
}
function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
function refreshAccessToken(sessionId, accessToken, refreshToken){
    return fetch("/payPage/common/v1/controller/sessions.php?sessionid="+sessionId+'&patch=', {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': accessToken
        },
        method: "post",
        body: JSON.stringify({
            "refreshToken": refreshToken
        })
    })
    .then((result) => {
        console.log(result);
        if(!result.ok){
            throw "Refresh Access Token failed: "+result.text();
        }
        return result.json()
    })

}
function registerUser(){
    var form = document.getElementById('registerUserForm');

    if(!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
    }else{
        return fetch("/payPage/common/v1/controller/users.php", {
            headers: {
                'Content-Type': 'application/json'
            },
            method: "post",
            body: JSON.stringify({
                "firstName": document.getElementById('firstName').value,
                "lastName": document.getElementById('lastName').value,
                "email": document.getElementById('customerUserName').value,
                "userName": document.getElementById('customerUserName').value,
                "password": document.getElementById('customerPassword').value,
            })
        })
        .then((result) => {
            console.log(result);
            if(result.ok){
                return result.json()
            }else{
                throw "unauthorised"
            }
        })
        .then((json)=>{
            console.log(json);
            onAccountCreated(json.data.id)
        })
        .catch(error => {
            console.log("ERROR: "+error);
            // TODO - show error to screen
        })
    }
}
function login(){
    var form = document.getElementById('loginForm');

    if(!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
    }else{
        return fetch("/payPage/common/v1/controller/sessions.php", {
            headers: {
                'Content-Type': 'application/json'
            },
            method: "post",
            body: JSON.stringify({
                "userName": document.getElementById('userName').value,
                "password": document.getElementById('password').value,
            })
        })
        .then((result) => {
            console.log(result);
            if(result.ok){
                return result.json()
            }else{
                throw "unauthorised"
            }
        })
        .then((result)=>{
            onSuccessfulLogin(result);
        })
        .catch(error => {
            console.log("ERROR: "+error);
            // TODO - show error to screen
        })
    }
}
function logout(){
    return fetch("/payPage/common/v1/controller/sessions.php?sessionid="+getCookie('sessionId'), {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': getCookie('accessToken')
        },
        method: "delete",
    })
    .then((result) => {
        console.log(result);
        if(result.ok){
            return result.json()
        }else{
            throw "unauthorised"
        }
    })
    .then((result)=>{
        onSuccessfulLogout(result);
    })
    .catch(error => {
        console.log("ERROR: "+error);
        // TODO - show error to screen
    })
}
function deleteCookies(path){
    document.cookie = "accessTokenExpires=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path="+path+";";
    document.cookie = "refreshTokenExpires=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path="+path+";";
    document.cookie = "sessionId=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path="+path+";";
    document.cookie = "accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path="+path+";";
    document.cookie = "refreshToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path="+path+";";
    document.cookie = "fullName=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path="+path+";";
    document.cookie = "email=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path="+path+";";
}