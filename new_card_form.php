
        <div id="paymentDetailsSection">
            <div id="cardDetailsSection">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <label class="form-check-label" for="number-container">Card Number</label>
                        <div id="number-container" class="form-control form-control-sm"></div>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-check-label" for="expiryDate">Expires</label>
                        <input class="expiry form-control" id="expiryDate" type="text" placeholder="MM/YY" pattern="[0-1][0-9]\/[2][1-9]" inputmode="numeric" autocomplete="off" autocorrect="off" spellcheck="off" aria-invalid="false" aria-placeholder="MM/YY" required>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-2">
                    <label id="securityCodeLabel" class="form-check-label" for="securityCode-container">Security Code</label>
                    <div id="securityCode-container" class="form-control form-control-sm"></div>
                </div>
            </div>
            <div id="storeCardSection" class="row">
                <div class="col-sm-5">
                    <input type="checkbox" class="form-check-input" id="storeCard" name="storeCard" value="1">
                    <label for="storeCard" class="form-check-label">Store my details for future use</label>
                </div>
            </div>
            <form id="billingForm" class="needs-validation" novalidate style="display: block">
                <div id="billingSection">
                    <h5>Card Billing Address</h5>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_forename" type="text" class="form-control form-control-sm" value="" placeholder="First name" required>
                                <label for="bill_to_forename" class="form-label">First name*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_surname" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" required>
                                <label for="bill_to_surname" class="form-label">Last name*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_address_line1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" required>
                                <label for="bill_to_address_line1" class="form-label">Address line 1*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_address_line2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address">
                                <label for="bill_to_address_line2" class="form-label">Address line 2</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_address_city" type="text" class="form-control form-control-sm" value="" placeholder="City/County" required>
                                <label for="bill_to_address_city" class="form-label">City/County*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_postcode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" required>
                                <label for="bill_to_postcode" class="form-label">PostCode*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group form-floating mb-3">
                                <select id="bill_to_address_country" class="form-control form-control-sm">
<?php
foreach ($countries as $key => $value) {
    echo "<option value=\"". $key ."\">" . $value . "</option>\n";
}
?>
                                </select>
                                <label for="bill_to_address_country" class="form-label">Country*</label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
