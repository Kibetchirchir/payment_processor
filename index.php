<?php
/**
 * Created by PhpStorm.
 * User: langa
 * Date: 6/5/2018
 * Time: 2:11 PM
 */
include 'includes/DB.php';
include 'helpers/Response.php';
include 'helpers/ConfirmationCode.php';
include "helpers/AfricasTalkingGateway.php";
include 'helpers/fpdf/fpdf.php';
//include 'PHPMailer/PHPMailerAutoload.php';

if(!empty($_POST['function']))
    $function = $_POST['function'];

if(!empty($_GET['function']))
    $function = $_GET['function'];

if(!empty($_POST['function']))
    $function = $_POST['checkout'];

if(!empty($_REQUEST['function']))
    $function = $_REQUEST['function'];




function formatPhoneNumber($phoneNumber) {
    $phoneNumber = preg_replace('/[^\dxX]/', '', $phoneNumber);
    $phoneNumber = preg_replace('/^0/','254',$phoneNumber);

    $phoneNumber = $phone = preg_replace('/\D+/', '', $phoneNumber);

    return $phoneNumber;
}

$amount = $_REQUEST['Amount'];
$PhoneNumber = formatPhoneNumber($_REQUEST['PhoneNumber']);
$AccountReference = $_REQUEST['AccountReference'];
$TransactionDesc ='REVENUESURE';
//$TransactionDes = $_REQUEST['TransactionDesc'];
$email = $_REQUEST['email'];
$name = $_REQUEST['name'];
$payment_mode = $_REQUEST['paymentMode'];
$api_key = $_REQUEST['api_key'];
$returnUrl="https://processor.ticketsoko.com/index.php/?api_key=$api_key";

setcookie("ref",$AcountReference);
$sql = "SELECT * FROM `configuration` WHERE `api_key`='$api_key'";
$result = DB::instance()->executeSQL($sql);
$urlReturn = $result->fetch_assoc()['returnURL'];

$sql = "SELECT * FROM `configuration` WHERE `api_key`='$api_key'";
$result = DB::instance()->executeSQL($sql);
$successURL=$result->fetch_assoc()['successURL'];

$sql = "SELECT * FROM `configuration` WHERE `api_key`='$api_key'";
$result = DB::instance()->executeSQL($sql);
$failedURL=$result->fetch_assoc()['failedURL'];

                
if ($result->num_rows > 0) {
    $response = new Response();
    $response->data = $result->fetch_assoc();
    $consumer_key = $response->data['consumer_key'];
    $consumer_secret = $response->data['consumer_secret'];
    $pass_key = $response->data['pass_key'];
    
    if($payment_mode == 'mpesa'){
        $sql = "select `configuration`.`short_code` from `configuration` where `configuration`.`api_key`='$api_key'";
        $result = DB::instance()->executeSQL($sql);
        $PayBillNumber = $result->fetch_assoc()['short_code'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://payme.ticketsoko.com/api/index.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"function\"\r\n\r\nCustomerPayBillOnline\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"PayBillNumber\"\r\n\r\n$PayBillNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"Amount\"\r\n\r\n$amount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"PhoneNumber\"\r\n\r\n$PhoneNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"AccountReference\"\r\n\r\n$AccountReference\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"TransactionDesc\"\r\n\r\n$TransactionDesc\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",

                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: 4fe6b48a-5c0a-e9fa-7d45-172ce8b64722"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        
        sleep(60);

        $sql="SELECT * FROM `transactions` WHERE `account_to`='$PayBillNumber' AND `amount`='$amount' AND `ref`='$AccountReference' AND `account_from`='$PhoneNumber' AND `status`='success'";
        $result = DB::instance()->executeSQL($sql);
        $count=mysqli_num_rows($result);

        if ($count > 0) {
        
                $response = new Response();
                $response->status = 200;
                $response->message = "payment made";
                $response->success = true;
                echo json_encode($response);
            
        }else
        {
                $response = new Response();
                $response->status = 400;
                $response->message = "payment not made";
                $response->success = false;
                echo json_encode($response);
        }
                
        
    }

    //check the payment option
    if ($payment_mode == 'Mpesa') {
        $sql = "select `configuration`.`short_code` from `configuration` where `configuration`.`api_key`='$api_key'";
        $result = DB::instance()->executeSQL($sql);
        $PayBillNumber = $result->fetch_assoc()['short_code'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://payme.ticketsoko.com/api/index.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"function\"\r\n\r\nCustomerPayBillOnline\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"PayBillNumber\"\r\n\r\n$PayBillNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"Amount\"\r\n\r\n$amount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"PhoneNumber\"\r\n\r\n$PhoneNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"AccountReference\"\r\n\r\n$AccountReference\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"TransactionDesc\"\r\n\r\n$TransactionDesc\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",

                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: 4fe6b48a-5c0a-e9fa-7d45-172ce8b64722"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        ?>
        
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <style>
            .loader {
              margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
              border: 16px solid #f3f3f3;
              border-radius: 100%;
              border-top: 16px solid #3498db;
              width: 120px;
              height: 120px;
              -webkit-animation: spin 2s linear infinite; /* Safari */
              animation: spin 2s linear infinite;
            }
            
            /* Safari */
            @-webkit-keyframes spin {
              0% { -webkit-transform: rotate(0deg); }
              100% { -webkit-transform: rotate(360deg); }
            }
            
            @keyframes spin {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
            }
            </style>

            <title>Payments</title>
        </head>

        <body>
            <b><font face="verdana"><center><h4>CHECK YOUR PHONE TO COMPLETE THE TRANSACTION</h4><center></b>   
        
        <?php
        //echo "<script type='text/javascript'>alert('check your phone. To complete the transaction')</script>";
        if($api_key==ac3553de0dd3688c28750e226b9efcfb7bc2ef7c){
            $url='https://sandbox.nouveta.co.ke/Deacons/opc-checkout/index';
        }
        else
        {
            echo file_get_contents($failedURL);
        }
        ?>
        <div class="loader"></div>
        </body>
        </html>
        <?php
       sleep(60);

        $sql="SELECT * FROM `transactions` WHERE `account_to`='$PayBillNumber' AND `amount`='$amount' AND `ref`='$AccountReference' AND `account_from`='$PhoneNumber' AND `status`='success'";
        $result = DB::instance()->executeSQL($sql);
        $count=mysqli_num_rows($result);

        if ($count > 0) {
            if ($urlReturn == 1) {

                $url="<script> window.location.replace('$successURL')</script>";

            } else {
                $response = new Response();
                $response->status = 200;
                $response->message = "payment made";
                $response->success = true;
                $url=json_encode($response);

            }

        } else {
            if ($urlReturn== 1) {

                $url= "<script> window.location.replace('$failedURL')</script>";
              echo "<script type='text/javascript'>alert('payments failed')</script>";

            } else {
                $response = new Response();
                $response->status= 404;
                $response->message = "transaction not completed";
                $response->success = false;
                $url=json_encode($response);


            }

        }
    }

    //card
    if(($payment_mode == 'card') or (array_key_exists("vpc_SecureHash", $_GET))){

        // echo "its  a card";
        if($payment_mode=='card')

        {
            $cardExpiryYear = $_REQUEST['cardexpiringyear'];
            $cardExpiryMonth = $_REQUEST['cardexpiringmonth'];
            $cardNumber = $_REQUEST['cardnumber'];
            $cardCvv = $_REQUEST['cardCvv'];
            $orderInfo =$AccountReference;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            set_time_limit(0);
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://cardpayments.ticketsoko.com/api/step1.php",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cvv\"\r\n\r\n$cardCvv\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"email\"\r\n\r\n$email\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"phone\"\r\n\r\n$PhoneNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"names\"\r\n\r\n$name\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"orderNumber\"\r\n\r\n$orderInfo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cardno\"\r\n\r\n$cardNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"ccexpmonth\"\r\n\r\n$cardExpiryMonth\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"ccexpyear\"\r\n\r\n$cardExpiryYear\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$amount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition:form-data; name=\"returnURL\"\r\n\r\n$returnUrl\r\n
                ",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",

                    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                    "postman-token: 4fe6b48a-5c0a-e9fa-7d45-172ce8b64722"
                ),
            ));

            $resp = curl_exec($curl);

            echo $resp;

        }
        else
        {
            $SECURE_SECRET = "EB7F770792C986E7FEE11118FF6F04E0";
            $servername = "localhost";
            $username = "ticketso_nouveta";
            $password = "Network97";
            $dbname = "ticketso_payme";

// Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $errorExists = false;



            function mail_template($name, $subject, $message){
                $messageee ='
<style>  
.header_top{ background-color:#37a5ff; color:#FFF; font-size:24px; padding:10px 10px 10px 10px; margin:0px; }
.subject{ background-color:#E5E5E5; color:#000; font-size:18px; padding:5px 10px 5px 10px;    }
.message{ padding:10px; font-size:16px;  }

.footaer{ font-size:14px; padding:10px;  background-color:#37a5ff; color:#FFF; text-align:center; }
</style>

<table width="100%" style=" font-family:\'Trebuchet MS\', Arial, Helvetica, sans-serif; padding:0px;margin:0px;">
<tr><td> <div class="header_top" style="background-color:#37a5ff; color:#FFF; font-size:24px; padding:10px 10px 10px 10px; margin:0px;" > </div> </td></tr>
<tr><td>
<div class="subject" style="background-color:#E5E5E5; color:#000; font-size:14px; padding:5px 10px 5px 10px;" > '.$subject.' </div>
 <div class="message" style="padding:10px; font-size:13px; ">
<p> '.$message.' </p>
 </div> </td></tr>
<tr><td> <div class="footaer" style="font-size:14px; padding:10px;  background-color:#37a5ff; color:#FFF; text-align:center;"  > <a style="color:#ffffff;" >Generated by</a> <a href="http://nouveta.tech">Nouveta Tech</a></div> </td></tr>
</table>
 ';
                return $messageee;
            }

            function send($subject, $message ,$to, $name,$from){
                $from = 'noreply@nouveta.tech';
                $name='Nouveta Payments';
                $headers = "From:" . $from;
                $message = mail_template($name, $subject, $message);
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: <noreply@nouveta.tech>' . "\r\n";
                mail($to,$subject,$message,$headers,'-fadmin@nouveta.tech');
            }



            function getResponseDescription($responseCode) {

                switch ($responseCode) {
                    case "0" : $result = "Transaction Successful"; break;
                    case "?" : $result = "Transaction status is unknown"; break;
                    case "1" : $result = "Unknown Error"; break;
                    case "2" : $result = "Bank Declined Transaction"; break;
                    case "3" : $result = "No Reply from Bank"; break;
                    case "4" : $result = "Expired Card"; break;
                    case "5" : $result = "Insufficient funds"; break;
                    case "6" : $result = "Error Communicating with Bank"; break;
                    case "7" : $result = "Payment Server System Error"; break;
                    case "8" : $result = "Transaction Type Not Supported"; break;
                    case "9" : $result = "Bank declined transaction (Do not contact Bank)"; break;
                    case "A" : $result = "Transaction Aborted"; break;
                    case "C" : $result = "Transaction Cancelled"; break;
                    case "D" : $result = "Deferred transaction has been received and is awaiting processing"; break;
                    case "F" : $result = "3D Secure Authentication failed"; break;
                    case "I" : $result = "Card Security Code verification failed"; break;
                    case "L" : $result = "Shopping Transaction Locked (Please try the transaction again later)"; break;
                    case "N" : $result = "Cardholder is not enrolled in Authentication scheme"; break;
                    case "P" : $result = "Transaction has been received by the Payment Adaptor and is being processed"; break;
                    case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
                    case "S" : $result = "Duplicate SessionID (OrderInfo)"; break;
                    case "T" : $result = "Address Verification Failed"; break;
                    case "U" : $result = "Card Security Code Failed"; break;
                    case "V" : $result = "Address Verification and Card Security Code Failed"; break;
                    default  : $result = "Unable to be determined";
                }
                return $result;
            }


            function getStatusDescription($statusResponse) {
                if ($statusResponse == "" || $statusResponse == "No Value Returned") {
                    $result = "3DS not supported or there was no 3DS data provided";
                } else {
                    switch ($statusResponse) {
                        Case "Y"  : $result = "The cardholder was successfully authenticated."; break;
                        Case "E"  : $result = "The cardholder is not enrolled."; break;
                        Case "N"  : $result = "The cardholder was not verified."; break;
                        Case "U"  : $result = "The cardholder's Issuer was unable to authenticate due to some system error at the Issuer."; break;
                        Case "F"  : $result = "There was an error in the format of the request from the merchant."; break;
                        Case "A"  : $result = "Authentication of your Merchant ID and Password to the ACS Directory Failed."; break;
                        Case "D"  : $result = "Error communicating with the Directory Server."; break;
                        Case "C"  : $result = "The card type is not supported for authentication."; break;
                        Case "S"  : $result = "The signature on the response received from the Issuer could not be validated."; break;
                        Case "P"  : $result = "Error parsing input from Issuer."; break;
                        Case "I"  : $result = "Internal Payment Server system error."; break;
                        default   : $result = "Unable to be determined"; break;
                    }
                }
                return $result;
            }

//  -----------------------------------------------------------------------------


            function addDigitalOrderField($field, $value) {

                if (strlen($value) == 0) return false;      // Exit the function if no $value data is provided
                if (strlen($field) == 0) return false;      // Exit the function if no $value data is provided

                // Add the digital order information to the data to be posted to the Payment Server
                $postData .= (($postData=="") ? "" : "&") . urlencode($field) . "=" . urlencode($value);

                // Add the key's value to the MD5 hash input (only used for 3 party)
                $hashinput .= $field . "=" . $value . "&";

                return $hashinput;

            }

            function sendSms($message,$phone){
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://payme.ticketsoko.com/api/sendsms.php",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"phoneNumber\"\r\n\r\n$phone\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"message\"\r\n\r\n$message\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
                    CURLOPT_HTTPHEADER => array(
                        "Cache-Control: no-cache",
                        "Postman-Token: 81d0cbd0-f7c1-f858-8986-e0a95de52158",
                        "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);
            }

            function callbackToClient($message,$order,$callback){
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $callback,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"transaction_id\"\r\n\r\n$order\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"message\"\r\n\r\n$message\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
                    CURLOPT_HTTPHEADER => array(
                        "Cache-Control: no-cache",
                        "Postman-Token: 81d0cbd0-f7c1-f858-8986-e0a95de52158",
                        "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
                    ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
            }

// If input is null, returns string "No Value Returned", else returns input
            function null2unknown($data) {
                if ($data == "") {
                    return "No Value Returned";
                } else {
                    return $data;
                }
            }



            function hashAllFields($hashinput,$SECURE_SECRET) {
                $hashinput=rtrim($hashInput,"&");
                $hashinput = strtoupper(hash_hmac('SHA256',$hashinput, pack("H*",$SECURE_SECRET)));
                return $hashinput;
            }


            if(array_key_exists("vpc_SecureHash", $_GET) )

            {

                //$md5HashData = $SECURE_SECRET;

                foreach($_GET as $key => $value) {
                    if (($key!="vpc_SecureHash") && ($key != "vpc_SecureHashType") && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_")))
                    {
                        $hashinput = addDigitalOrderField($key, $value);
//echo "$hashinput";

                    }


                }
//ksort($hashinput);
                $secureHash = hashAllFields($hashinput,$SECURE_SECRET);


                $hashValidated = "Valid";

                $amount          = null2unknown($_GET["vpc_Amount"]);
                $locale          = null2unknown($_GET["vpc_Locale"]);
                $batchNo         = null2unknown($_GET["vpc_BatchNo"]);
                $command         = null2unknown($_GET["vpc_Command"]);
                $message         = null2unknown($_GET["vpc_Message"]);
                $version         = null2unknown($_GET["vpc_Version"]);
                $cardType        = null2unknown($_GET["vpc_Card"]);
                $orderInfo       = null2unknown($_GET["vpc_OrderInfo"]);
                $receiptNo       = null2unknown($_GET["vpc_ReceiptNo"]);
                $merchantID      = null2unknown($_GET["vpc_Merchant"]);
                $authorizeID     = null2unknown($_GET["vpc_AuthorizeId"]);
                $merchTxnRef     = null2unknown($_GET["vpc_MerchTxnRef"]);
                $transactionNo   = null2unknown($_GET["vpc_TransactionNo"]);
                $acqResponseCode = null2unknown($_GET["vpc_AcqResponseCode"]);
                $txnResponseCode = null2unknown($_GET["vpc_TxnResponseCode"]);
                $amt=substr("$amount",0,-2);

// 3-D Secure Data
                $verType         = array_key_exists("vpc_VerType", $_GET)          ? $_GET["vpc_VerType"]          : "No Value Returned";
                $verStatus       = array_key_exists("vpc_VerStatus", $_GET)        ? $_GET["vpc_VerStatus"]        : "No Value Returned";
                $token           = array_key_exists("vpc_VerToken", $_GET)         ? $_GET["vpc_VerToken"]         : "No Value Returned";
                $verSecurLevel   = array_key_exists("vpc_VerSecurityLevel", $_GET) ? $_GET["vpc_VerSecurityLevel"] : "No Value Returned";
                $enrolled        = array_key_exists("vpc_3DSenrolled", $_GET)      ? $_GET["vpc_3DSenrolled"]      : "No Value Returned";
                $xid             = array_key_exists("vpc_3DSXID", $_GET)           ? $_GET["vpc_3DSXID"]           : "No Value Returned";
                $acqECI          = array_key_exists("vpc_3DSECI", $_GET)           ? $_GET["vpc_3DSECI"]           : "No Value Returned";
                $authStatus      = array_key_exists("vpc_3DSstatus", $_GET)        ? $_GET["vpc_3DSstatus"]        : "No Value Returned";

                $trxResp= getResponseDescription($txnResponseCode);
                $sql1 = "SELECT * FROM card_transactions where orderinfo='$orderInfo'";
                $result1 = $conn->query($sql1);
                $row1 = $result1->fetch_assoc();
                $callback =$row1['callback'];
                $cemail = $row1['cemail'];
                $cname = $row1['fullname'];
                $phone = $row1['phone'];




                if (($txnResponseCode == 0) && ($message=='Approved'))
                {
                    $status ='successful';
                    $msg="Dear $cname, \n Your order $orderInfo for value KSH $amt has successfuly been processed.  \n This charge will appear on your credit/debit card statement as Nouveta Limited \n Thank you";
                    //echo $status. $trxResp;
                      $url="<script> window.location.replace('$successURL')</script>";
                }

                else
                {
                    //echo "$trxResp";
                    $status ='failed';
                    $msg="Dear $cname,\n Your order $orderInfo for value KSH $amt has Failed. Message from Bank is $trxResp. \n Thank you";
                    //echo $status. $trxResp;

                    echo "<script type='text/javascript'>alert('payments failed check your email for details')</script>";
                    
                    $url="<script> window.location.replace('$failedURL')</script>";
                   


                }

                $upddate1=date('YmdHis');

                $upddate=date('Y-m-d H:i:s');
                $sql = "UPDATE card_transactions SET amount = '$amt', locale = '$locale', batchNo = '$batchNo', command = '$command', message = '$message', version = '$version', cardType = '$cardType', receiptNo = '$receiptNo', merchantID = '$merchantID', authorizeID = '$authorizeID', merchTxnRef = '$merchTxnRef', transactionNo = '$transactionNo', acqResponseCode = '$acqResponseCode', txnResponseCode = '$txnResponseCode', verType = '$verType', verStatus = '$verStatus', token = '$token', verSecurLevel = '$verSecurLevel', enrolled = '$enrolled', xid = '$xid', acqECI = '$acqECI', authStatus = '$authStatus', finstatus = '$status', upddate = '$upddate' WHERE orderinfo = '$orderInfo'";

                if($status='successfull'){
                    $mystatus=="Y";
                }
                if($status='failed'){
                    $mystatus=="F";
                }
                $sql1 = "INSERT INTO transactions (card_trans_type,card_trans_number, card_trans_time, card_trans_amount,bill_ref_number,card_names,status)
VALUES ('Card','".$transactionNo."', '".$upddate1."', '".$amt."', '".$orderInfo."', '".$cname."', '".$mystatus."')";

                $conn->query($sql1);

                if ($conn->query($sql) === TRUE) {
                    //echo "New record created successfully";
                } else {
                    // echo "Error: " . $sql . "<br>" . $conn->error;
                }

                $subject="Nouveta Payment Notification";
                $from ="";
                $name="";
                send($subject, $msg ,$cemail,$name,$from);

                sendSms($msg,$phone);
                $conn->close();



            }



            else
            {
                // Secure Hash was not validated, add a data field to be displayed later.
                $hashValidated = "Invalid";
                echo"All parameteres---- Negative";
            }





        }

    }
    
    /*
    *
    *
    *cash
    */
    if ($payment_mode == 'cash') {

        $event ='Laibon Orchestra';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://payme.ticketsoko.com/api/index.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"function\"\r\n\r\ncash\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"agentNo\"\r\n\r\n$event\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$amount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"PhoneNumber\"\r\n\r\n$PhoneNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"acc\"\r\n\r\n$AccountReference\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"TransactionDesc\"\r\n\r\n$TransactionDesc\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",

                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: 4fe6b48a-5c0a-e9fa-7d45-172ce8b64722"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        ?>
        <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>report</title>
    </head>

    <body>
    <h1>PLEASEWAIT.... </h1><br>
    </body>

    </html>

<?php
        //echo 'im here';
        echo "<script> window.location.replace('https://cash.ticketsoko.com/e/15/checkout/good')</script>";
    }
    
    if ($payment_mode == 'paybill') {

        $event ='Laibon Orchestra';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://payme.ticketsoko.com/api/index.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"function\"\r\n\r\npaybill\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"agentNo\"\r\n\r\n$event\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$amount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"PhoneNumber\"\r\n\r\n$PhoneNumber\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"acc\"\r\n\r\n$AccountReference\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"TransactionDesc\"\r\n\r\n$TransactionDesc\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",

                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: 4fe6b48a-5c0a-e9fa-7d45-172ce8b64722"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        ?>
        <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>report</title>
    </head>

    <body>
    <h1>PLEASEWAIT.... </h1><br>
    </body>

    </html>

<?php
        //echo 'im here';
        echo "<script> window.location.replace('https://cash.ticketsoko.com/e/15/checkout/good')</script>";
    }
    
    
    
    
}

else

//no payment mode stated
{
    /*client not found*/
    $response = new Response();
    $response->message = "API key configurations not found";
    $response->success = false;
    echo json_encode($response);
}



  if($function=="callback"){


    $ReceiptNo = $_POST['AccountReference'];
    $phone_number = formatPhoneNumber( $_POST['PhoneNumber']);
    $amount_paid = $_POST['Amount'];
    $code = $_POST['MpesaReceiptNumber'];
    $payment_method = "Mpesa";


    
    

    $url="<script> window.location.replace('$successURl')</script>";


}

  echo $url;

