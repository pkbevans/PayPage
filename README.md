# PayPage

Payment Page

This is a Payment Page Example built against the Cybersource Gateway.

It demonstrates the following Cybersource components:
- Flex Microform
- RESP API
- Tokenization (TMS)

Its written in PHP and Javascript.

You will need a Cybersource Sandbox Account to be able to use this.

Steps to enable it:

1. Sign up for Cybersource sandbox account here: https://developer.cybersource.com/hello-world/sandbox.html
2. Copy the Key and Secret key that is displayed after sign up
3. Create a file mkj
$keys = [
    "pemid03" => [
        'key_id' => "fa4143be-1234-436d-aa5c-f8fbbb4b6bfd",
        'secret_key'=> "rzabWA0SFyDXlVY/dTEA123456shJm/5IaNElBachZk="
    ]
];
3. Update PeRestLib/RestConstants.php:
   - Update KEYS_PATH =  "/ppSecure/";   // Replace with path to the PeRestLibKeys file.
   - Update MID = "barclayssitt00";      // Replace with MID (Can be PORTFOLIO or Account-level)
   - CHILD_MID = "paulspants45011";       // Replace with Transacting MID if using PORTFOLIO or Account-level mid in MID
   - PRODUCTION_TARGET_ORIGIN =  "bondevans.com";  // Replace with Production URL for non-localhost testing
   - LOCALHOST_TARGET_ORIGIN =  "site.test";   // Replace with your localhost HTTPS alias.  MUST BE HTTPS

5.    
