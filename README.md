# PayPage

Payment Page

This is a Payment Page Example built against the Cybersource Gateway.

It demonstrates the following Cybersource components:
- Flex Microform
- REST API
- Tokenization (TMS)

Its written in PHP and Javascript.

You will need a Cybersource Sandbox Account to be able to use this.

Steps to enable it:

1. Sign up for Cybersource sandbox account here: https://developer.cybersource.com/hello-world/sandbox.html
2. Copy the Key and Secret key that is displayed after sign up
3. Create a file called PeRestLibKeys.php file in this format, with REST key id and secret for your Cybs MID
$keys = [
    "<<YOUR CYBS MID>>" => [
        'key_id' => "<<YOUR REST KEY ID>>",
        'secret_key'=> "<<YOUR REST SECRET KEY>>"
    ]
];
3. Update PeRestLib/RestConstants.php:
   - Update KEYS_PATH =  "/ppSecure/";          // Replace with path to your PeRestLibKeys file.
   - Update MID = "<<YOUR CYBS MID>>";          // Replace with MID (Can be PORTFOLIO or Account-level)
   - CHILD_MID = "";                            // Can be left blank
   - PRODUCTION_TARGET_ORIGIN =  "";            // Replace with Production URL for non-localhost testing
   - LOCALHOST_TARGET_ORIGIN =  "site.test";    // Replace with your localhost HTTPS alias.  MUST BE HTTPS
    
There is a demo of this code at https://bondevans.com/payPage
