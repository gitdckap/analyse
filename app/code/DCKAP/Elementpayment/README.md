#Contents
##Synopsis
Dckap\Elementpayment module is responsible for Payment using Credit card via Element Payment Gateway

##Overview
Guest and registered customers without credit line have the option to pay through Credit/Debit cards. Since Magento itself is not the PCI Compliant an Iframe of Vantiv payment gateway is embedded in the payment process.

Store Card Data in PASS to Tokenize (and subsequently submit Sale using Token)

1. CWC application collects the non-sensitive transaction details such as the address information, etc., and submits a programmatic request using the TransactionSetup method in the interface specification (e.g. TransactionSetupMethod of 7/PaymentAccountCreate to store card data).
2. Element responds with a TransactionSetupID (a GUID) if the request was successful.
3. CWC application performs a redirect (full, popup, or iFrame, etc.) to our Hosted Payments URL and appends the TransactionSetupID to the end of that URL. For example, it might be: https://certtransaction.hostedpayments.com/?TransactionSetupID=INSERTHERE
4. The end user swipes or keys the card into the Hosted Payments page/popup and clicks Submit.
5. Element redirects the response details to the ReturnURL which CWC originally provided in thier initial TransactionSetup request from the first step (the response details, such as the PaymentAccountID reference pointer, in name/value pairs to the end of their ReturnURL will be appended).
6. CWC application receives the URL and parses out the response details, and can store the PaymentAccountID in an own application.
7. When CWC are ready to charge the card, simply submit the PaymentAccountID (instead of the card number) using the ExtendedParameters object/class of the CreditCardSale method, for example, and the transaction is processed like any normal sale.
8. Element responds with the appropriate response details, which CWC can then display on their own page/application.
