<?php

function coinpayments_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"CoinPayments.net"),
     "coinpayments_merchant" => array("FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "32", 'Description' => 'Your Merchant ID can be found under the account settings section'),
     "coinpayments_ipn_secret" => array("FriendlyName" => "IPN Secret", "Type" => "password", "Size" => "32", 'Description' => 'Your IPN Secret can be found under merchant settings, you generate it yourself'),
     "coinpayments_want_shipping" => array("FriendlyName" => "Include shipping information", "Type" => "yesno" ),
     "coinpayments_email" => array("FriendlyName" => "IPN Debug Email", "Type" => "text", "Size" => "32", ),
    );
    //     "instructions" => array("FriendlyName" => "Payment Instructions", "Type" => "textarea", "Rows" => "5", "Description" => "Do this then do that etc...", ),
	return $configarray;
}

function coinpayments_link($params) {

	$fields = array(
		'cmd' => '_pay_auto',
		'merchant' => $params['coinpayments_merchant'],
		'reset' => '1',
		'invoice' => $params['invoiceid'],
		'item_name' => $params["description"],
		'amountf' => $params['amount'],
		'currency' => $params['currency'],
		'email' => $params['clientdetails']['email'],
		'first_name' => $params['clientdetails']['firstname'],
		'last_name' => $params['clientdetails']['lastname'],
		'want_shipping' => $params['coinpayments_want_shipping'] ? '1':'0',
		'ipn_url' => $params['systemurl'].'/modules/gateways/callback/coinpayments.php',
		'success_url' => $params['systemurl'].'viewinvoice.php?id='.$params['invoiceid'].'&paymentsuccess=true',
		'cancel_url' => $params['systemurl'].'viewinvoice.php?id='.$params['invoiceid'].'&paymentfailed=true',
	);
	
	if ($params['coinpayments_want_shipping']) {
		$fields['address1'] = $params['clientdetails']['address1'];
		$fields['address2'] = $params['clientdetails']['address2'];
		$fields['city'] = $params['clientdetails']['city'];
		$fields['state'] = $params['clientdetails']['state'];
		$fields['zip'] = $params['clientdetails']['postcode'];
		$fields['country'] = $params['clientdetails']['country'];
		$fields['phone'] = $params['clientdetails']['phonenumber'];
	}

	# Enter your code submit to the gateway...

	$code = '<form id="cpsform" action="https://www.coinpayments.net/index.php" method="post">';
	foreach ($fields as $n => $v) {
		$code .= '<input type="hidden" name="'.$n.'" value="'.htmlspecialchars($v).'" />';
	}
	$code .= '<input type="image" src="https://www.coinpayments.net/images/pub/buynow-med-grey.png" alt="Pay Now with Bitcoin, Litecoin, and other cryptocurrencies...">';
	$code .= '</form>';
//	$code .= '<script type="text/javascript">document.forms["cpsform"].submit();</script>';

	return $code;
}
