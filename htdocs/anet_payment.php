<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>London Water Co-op - Payment</title>
</head>

<body onload="doIt();">

<?php
  require 'sdk-php-2.0.3/autoload.php';
  require_once 'Constants.php';
  use net\authorize\api\contract\v1 as AnetAPI;
  use net\authorize\api\controller as AnetController;

  define("AUTHORIZENET_LOG_FILE", "phplog");
  
function getAnAcceptPaymentPage()
{
    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName(\Constants::MERCHANT_LOGIN_ID);
    $merchantAuthentication->setTransactionKey(\Constants::MERCHANT_TRANSACTION_KEY);
    
    // Set the transaction's refId
    $refId = 'ref' . time();

    // Create order information
    $order = new AnetAPI\OrderType();
    $order->setDescription("Drinking Water");

    // Set the customer's Bill To address
    $customerAddress = new AnetAPI\CustomerAddressType();
    $customerAddress->setFirstName($_REQUEST["bill_to_forename"]);
    $customerAddress->setLastName($_REQUEST["bill_to_surname"]);
    $customerAddress->setAddress($_REQUEST["property_address"]);
    $customerAddress->setCity("Cottage Grove");
    $customerAddress->setState("OR");
    $customerAddress->setZip("97424");
    $customerAddress->setCountry("USA");
    $customerAddress->setEmail($_REQUEST["bill_to_email"]);

    // Set the customer's identifying information
    $customerData = new AnetAPI\CustomerDataType();
    $customerData->setType("individual");
    $customerData->setEmail($_REQUEST["bill_to_email"]);

    $lineItem1 = new AnetAPI\LineItemType();
    $lineItem1->setItemId("1");
    $lineItem1->setName("Charge for Water Used");
    $lineItem1->setDescription("Charges for Water Used at ".$_REQUEST["property_address"]);
    $lineItem1->setQuantity("1");
    $lineItem1->setUnitPrice($_REQUEST["amount"]);
    $lineItem1->setTaxable(0);
    
    $lineItem2 = new AnetAPI\LineItemType();
    $lineItem2->setItemId("2");
    $lineItem2->setName("Convenience fee");
    $lineItem2->setDescription("What it costs LWC to provide online payments for this transaction");
    $lineItem2->setQuantity("1");
    $lineItem2->setUnitPrice($_REQUEST["cost"]);
    $lineItem2->setTaxable(0);

    $lineItems[]=$lineItem1;
    $lineItems[]=$lineItem2;
    
    // Set Hosted Form options
    $setting1 = new AnetAPI\SettingType();
    $setting1->setSettingName("hostedPaymentButtonOptions");
    $setting1->setSettingValue("{\"text\": \"Pay\"}");

    $setting2 = new AnetAPI\SettingType();
    $setting2->setSettingName("hostedPaymentOrderOptions");
    $setting2->setSettingValue("{\"show\": true, \"merchantName\":\"London Water Co-op\"}");

    $setting3 = new AnetAPI\SettingType();
    $setting3->setSettingName("hostedPaymentReturnOptions");
    $setting3->setSettingValue(
        "{\"url\": \"https://londonwatercoop.org\", \"cancelUrl\": \"https://londonwatercoop.org\", \"showReceipt\": true}"
    );
    
    $setting4 = new AnetAPI\SettingType();
    $setting4->setSettingName("hostedPaymentCustomerOptions");
    $setting4->setSettingValue(
        "{\"showEmail\": true, \"requiredEmail\": true, \"addPaymentProfile\": false}" );
    
    $setting5 = new AnetAPI\SettingType();
    $setting5->setSettingName("hostedPaymentShippingAddressOptions");
    $setting5->setSettingValue( '{"show": false, "required": false}' );
    
    $setting6 = new AnetAPI\SettingType();
    $setting6->setSettingName("hostedPaymentBillingAddressOptions");
    $setting6->setSettingValue('{"show": true, "required": false}');

    // Build transaction request
    $request = new AnetAPI\GetHostedPaymentPageRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);

    //create a transaction
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("authCaptureTransaction");
    $transactionRequestType->setAmount($_REQUEST["total"]);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setBillTo($customerAddress);
    $transactionRequestType->setCustomer($customerData);
    $transactionRequestType->setLineItems($lineItems);

    $request->setTransactionRequest($transactionRequestType);

    $request->addToHostedPaymentSettings($setting1);
    $request->addToHostedPaymentSettings($setting2);
    $request->addToHostedPaymentSettings($setting3);
    $request->addToHostedPaymentSettings($setting4);
    $request->addToHostedPaymentSettings($setting5);
    $request->addToHostedPaymentSettings($setting6);
    
    //execute request
    $controller = new AnetController\GetHostedPaymentPageController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
    
    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo $response->getToken()."\n";
    } else {
        echo "ERROR :  Failed to get hosted payment page token\n";
        $errorMessages = $response->getMessages()->getMessage();
        echo "RESPONSE : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
    }
    return $response;
}

?>

<script>
function doIt()
{
  //alert("just waiting");
  document.getElementById("payment_confirmation").submit();
}
</script>
<form id="payment_confirmation" action="https://accept.authorize.net/payment/payment" method="post"/>
<input type="hidden" name="token" value="<?php getAnAcceptPaymentPage()->getToken();?>" />
  
<input type="submit" value="."/>
</form>
</body>
</html>
