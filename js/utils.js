function formatNameAddress(nameAddress){
    return xtrim(nameAddress.firstName, " ") +
            xtrim(nameAddress.lastName, "<br>") +
            xtrim(nameAddress.address1, "<br>") +
            xtrim(nameAddress.address2, "<br>") +
            xtrim(nameAddress.locality, "<br>") +
            xtrim(nameAddress.postalCode, "<br>") +
            xtrim(nameAddress.country, "");
}
function xtrim(xin, suffix){
    xout = xin.trim().replace(/,*$/, "");
    return (xout===""? "" : xout + suffix) ;
}
