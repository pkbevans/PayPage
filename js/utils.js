function formatNameAddress(nameAddress){
    return xtrim(nameAddress.firstName, " ") +
            xtrim(nameAddress.lastName, ", ") +
            xtrim(nameAddress.address1, ", ") +
            xtrim(nameAddress.address2, ", ") +
            xtrim(nameAddress.locality, ", ") +
            xtrim(nameAddress.postalCode, ", ") +
            xtrim(nameAddress.country, "");
}
function xtrim(xin, suffix){
    if(xin == null){
        return "";
    }
    xout = xin.trim().replace(/,*$/, "");
    return (xout===""? "" : xout + suffix) ;
}
