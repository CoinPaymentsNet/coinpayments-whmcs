# CoinPayments.net plugin for WHMCS
For license details see license.txt

Installation Instructions:
1. In your WHMCS root folder, upload the contents of the upload directory. You should end up with 2 files in these paths (relative to WHMCS root folder):
	modules/gateways/coinpayments.php
	modules/gateways/callback/coinpayments.php

2. Log in to your WHMCS admin panel and go to Setup, Payments, Payment Gateways.

3. In the Activate Module dropdown, select CoinPayments.net and click the Activate button.
		
4. In the CoinPayments.net configuration form enter your Merchant ID and your IPN Secret (from My Account -> Edit Settings.)

5. Optionally fill in an IPN Debug Email address to receive notices about invalid IPNs. [Recommend you do for at least the 1st couple of transactions for testing purposes.]

6. Click 'Save Changes' and you are good to go.
