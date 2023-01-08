function formatNameAddress(nameAddress){
    return "<p class=\"fs-6\">" + "<b>" + xtrim(nameAddress.firstName, " ") +
            xtrim(nameAddress.lastName, "</b><br>") +
            xtrim(nameAddress.address1, ", ") +
            xtrim(nameAddress.address2, ", ") +
            xtrim(nameAddress.locality, ", ") +
            xtrim(nameAddress.administrativeArea, ", ") +
            xtrim(nameAddress.postalCode, ", ") +
            xtrim(nameAddress.country, "</p>");
}
function xtrim(xin, suffix){
    if(xin == null){
        return "";
    }
    xout = xin.trim().replace(/,*$/, "");
    return (xout===""? "" : xout + suffix) ;
}
