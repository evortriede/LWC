<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>London Water Co-op - Payment</title>
</head>

<body onload="doIt();">

<?php
define ('HMAC_SHA256', 'sha256');

include 'payment_creds.php';

function sign ($params) {
  return signData(buildDataToSign($params), SECRET_KEY);
}

function signData($data, $secretKey) {
    return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
}

function buildDataToSign($params) {
        $signedFieldNames = explode(",",$params["signed_field_names"]);
        foreach ($signedFieldNames as $field) {
           $dataToSign[] = $field . "=" . $params[$field];
        }
        return commaSeparate($dataToSign);
}

function commaSeparate ($dataToSign) {
    return implode(",",$dataToSign);
}

$params["access_key"] = ACCESS_KEY;
$params["profile_id"] = PROFILE_ID;
$params["transaction_uuid"] = uniqid();
$params["signed_field_names"] = "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency";
$params["unsigned_field_names"] = "bill_to_forename,bill_to_surname,bill_to_email,display_forename,display_surname,display_email,display_amount,property_address,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_postal_code,bill_to_address_country,ship_to_address_line1";
$params["signed_date_time"] = gmdate("Y-m-d\TH:i:s\Z");
$params["locale"] = "en";
$params["transaction_type"] = "sale";
$params["reference_number"] = uniqid();
$params["amount"] = $_REQUEST["total"];
$params["currency"] = "USD";
?>

<script>
function doIt()
{
  //alert("just waiting");
  document.getElementById("payment_confirmation").submit();
}
</script>
<form id="payment_confirmation" action="<?php echo ENDPOINT;?>" method="post"/>
    <?php
        foreach($params as $name => $value) {
            echo "<input type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
        }
        echo "<input type=\"hidden\" id=\"signature\" name=\"signature\" value=\"" . sign($params) . "\"/>\n";
    ?>
    <input type="hidden" id="bill_to_email" name="bill_to_email" value="<?php echo $_REQUEST["bill_to_email"];?>"/>
    <input type="hidden" id="bill_to_forename" name="bill_to_forename" value="<?php echo $_REQUEST["bill_to_forename"];?>"/>
    <input type="hidden" id="bill_to_surname" name="bill_to_surname" value="<?php echo $_REQUEST["bill_to_surname"];?>"/>
    <input type="hidden" id="bill_to_address_line1" name="bill_to_address_line1" value="<?php echo $_REQUEST["property_address"];?>"/>
    <input type="hidden" id="ship_to_address_line1" name="ship_to_address_line1" value="<?php echo $_REQUEST["property_address"];?>"/>
    <input type="hidden" id="bill_to_address_city" name="bill_to_address_city" value="Cottage Grove"/>
    <input type="hidden" id="bill_to_address_state" name="bill_to_address_state" value="OR"/>
    <input type="hidden" id="bill_to_address_postal_code" name="bill_to_address_postal_code" value="97424"/>
    <input type="hidden" id="bill_to_address_country" name="bill_to_address_country" value="US"/>
    
    <fieldset>
      <legend>Payment Details - Please confirm</legend>
        First name: <input type="text" id="display_forename" name="display_forename" value="<?php echo $_REQUEST["bill_to_forename"];?>"/><br>
        Last name: <input type="text" id="display_surname" name="display_surname" value="<?php echo $_REQUEST["bill_to_surname"];?>"/><br>
        Email: <input type="text" id="display_email" name="display_email" value="<?php echo $_REQUEST["bill_to_email"];?>"/><br>
        Address: <input type="text" id="property_address" name="property_address" value="<?php echo $_REQUEST["property_address"];?>"/><br>
        Total: <input type="text" id="display_amount" name="display_amount" value="<?php echo $_REQUEST["total"];?>"/><br>
    </fieldset>
<input type="submit" value="Confirm"/>
</form>
</body>
</html>
