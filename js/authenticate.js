function authenticate(){
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
                        document.cookie = "accessTokenExpires=" + t+';expires=;path=/';
                        t = new Date();
                        t.setSeconds(t.getSeconds() + result.data.refreshTokenExpiresIn);
                        document.cookie = "refreshTokenExpires=" + t+';expires=;path=/';
                        document.cookie = "sessionId=" + result.data.sessionId+';expires=;path=/';
                        newAccessToken = result.data.accessToken;
                        newRefreshToken = result.data.refreshToken;
                        document.cookie = "accessToken=" + newAccessToken+';expires=;path=/';
                        document.cookie = "refreshToken=" + newRefreshToken+';expires=;path=/';
                        fullName = result.data.firstName + " " + result.data.lastName;
                        document.getElementById("username").innerHTML=fullName;
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
    return fetch("/payPage/v1/controller/sessions.php?sessionid="+sessionId+'&patch=', {
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
function login(){
    var form = document.getElementById('loginForm');

    if(!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
    }else{
        return fetch("/payPage/v1/controller/sessions.php", {
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
            console.log(result);
            var t = new Date();
            t.setSeconds(t.getSeconds() + result.data.accessTokenExpiresIn);
            document.cookie = "accessTokenExpires=" + t+';expires=;path=/';
            t = new Date();
            t.setSeconds(t.getSeconds() + result.data.refreshTokenExpiresIn);
            document.cookie = "refreshTokenExpires=" + t+';expires=;path=/';
            document.cookie = "sessionId=" + result.data.sessionId+';expires=;path=/';
            document.cookie = "accessToken=" + result.data.accessToken+';expires=;path=/';
            document.cookie = "refreshToken=" + result.data.refreshToken+';expires=;path=/';
            fullName = result.data.firstName + " " + result.data.lastName;
            document.getElementById("username").innerHTML=fullName;
            document.getElementById("loginSection").style.display="none"
            document.getElementById("contentSection").style.display="block"
        })
        .catch(error => {
            console.log("ERROR: "+error);
            // TODO - show error to screen
        })
    }
}
