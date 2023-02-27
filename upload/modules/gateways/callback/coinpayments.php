<?php

use WHMCS\Billing\Invoice;
use WHMCS\Billing\Payment\Transaction;

require_once(__DIR__ . '/../../../init.php');

App::load_function('gateway');

$gatewaymodule = "coinpayments"; # Enter your gateway module name here replacing template
$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"]) die("Module Not Activated"); # Checks gateway module is active before accepting callback

function coinpayments_error($msg) {
	global $GATEWAY;
	
	if (!empty($GATEWAY['coinpayments_email'])) {
		$report = "Invalid IPN Received\n\n";
				
		if ($msg) {
			$report .= "Error Message: ".$msg."\n\n";
		}
			
		$report .= "POST Fields\n\n";
		foreach ($_POST as $key => $value) {
			$report .= $key . '=' . $value. "\n";
		}
			
		mail($GATEWAY['coinpayments_email'], "CoinPayments.net Invalid IPN", $report);
	}
	die('IPN Error: '.$msg);
}

if (isset($_POST['ipn_mode']) && $_POST['ipn_mode'] == 'hmac') {
	if (isset($_SERVER['HTTP_HMAC']) && !empty($_SERVER['HTTP_HMAC'])) {
		$request = file_get_contents('php://input');
		if ($request !== FALSE && !empty($request)) {
			$hmac = hash_hmac("sha512", $request, trim($GATEWAY['coinpayments_ipn_secret']));
			if ($hmac != $_SERVER['HTTP_HMAC']) {
				coinpayments_error('HMAC signature does not match');
			}
		} else {
			coinpayments_error('Error reading POST data');
		}
	} else {
		coinpayments_error('No HMAC signature sent.');
	}
} else {
	coinpayments_error("Unsupported IPN verification mode!");
}

# Get Returned Variables - Adjust for Post Variable Names from your Gateway's Documentation
$ipn_type = $_POST["ipn_type"];
$merchant = $_POST["merchant"];
$status = intval($_POST["status"]);
$status_text = $_POST["status_text"];
$invoiceid = $_POST["invoice"];
$transid = $_POST["txn_id"];
$amount1 = floatval($_POST["amount1"]);
$amount2 = floatval($_POST["amount2"]);
$currency1 = $_POST["currency1"];
$currency2 = $_POST["currency2"];
	
if ($ipn_type != "button" && $ipn_type != "simple") {
	coinpayments_error("ipn_type != button or simple");
}
if ($merchant != trim($GATEWAY['coinpayments_merchant'])) {
	coinpayments_error("Invalid merchant ID!");
}
if ($amount1 <= 0) {
	coinpayments_error("Amount must be > 0!");
}

$invoice = Invoice::find($invoiceid);
if (is_null($invoice)) {
	coinpayments_error('No invoice found');
}

// Ensure payment currency matches invoice currency!
if ($invoice->getCurrencyCodeAttribute() != $currency1) {
	coinpayments_error('Payment currency does not match invoice currency');
}

checkCbTransID($transid);

if ($status >= 100 || $status == 2) {
	logTransaction($GATEWAY["name"], $_POST, 'Payment Completed');
	$invoice->addPaymentIfNotExists($amount1, $transid, 0, $gatewaymodule);
} else if ($status >= 0) {
	if ($invoice->getBalanceAttribute()) {
		$invoice->status = 'Payment Pending';
		$invoice->save();
	}
	
	logTransaction($GATEWAY["name"],$_POST, 'Payment Pending: '.$status_text);
} else {
	// Gateway Log
	logTransaction($GATEWAY["name"], $_POST, 'Payment Error: '.$status_text);
	
	// Set invoice status to pending if invoice has a balance
	if ($invoice->getBalanceAttribute()) {
		$invoice->status = 'Unpaid';
		$invoice->save();
	}
}

die('IPN OK');
