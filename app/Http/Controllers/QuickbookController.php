<?php

namespace App\Http\Controllers;

use App\Models\ClientDetails;
use App\Models\Invoice as ModelsInvoice;
use App\Models\Payment as ModelsPayment;
use App\Models\QuickBooksSetting;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Payment;

class QuickbookController extends AccountBaseController
{

    const INCOME_ACCOUNT_TYPE = 'Income';
    const INCOME_ACCOUNT_SUBTYPE = 'SalesOfProductIncome';

    public function __construct()
    {
        parent::__construct();
        $this->quickBooksSetting = QuickBooksSetting::first();
        $this->quickBooksEnvironment = $this->quickBooksSetting->environment;
        $this->quickBooksAccessToken = $this->quickBooksSetting->access_token;
        $this->quickBooksRefreshToken = $this->quickBooksSetting->refresh_token;
        $this->quickBooksRealmId = $this->quickBooksSetting->realmid;
        $this->quickBooksClientId = $this->quickBooksEnvironment == 'Development' ? $this->quickBooksSetting->sandbox_client_id : $this->quickBooksSetting->client_id;
        $this->quickBooksClientSecret = $this->quickBooksEnvironment == 'Development' ? $this->quickBooksSetting->sandbox_client_secret : $this->quickBooksSetting->client_secret;
    }

    public function getCredentials()
    {
        $this->quickBooksSetting = QuickBooksSetting::first();
        $this->quickBooksEnvironment = $this->quickBooksSetting->environment;
        $this->quickBooksAccessToken = $this->quickBooksSetting->access_token;
        $this->quickBooksRefreshToken = $this->quickBooksSetting->refresh_token;
        $this->quickBooksRealmId = $this->quickBooksSetting->realmid;
        $this->quickBooksClientId = $this->quickBooksEnvironment == 'Development' ? $this->quickBooksSetting->sandbox_client_id : $this->quickBooksSetting->client_id;
        $this->quickBooksClientSecret = $this->quickBooksEnvironment == 'Development' ? $this->quickBooksSetting->sandbox_client_secret : $this->quickBooksSetting->client_secret;
    }

    public function index()
    {
        if ($this->quickBooksClientId == '' || $this->quickBooksClientSecret == '') {
            return redirect()->back()->withErrors(['credential_error' => __('messages.quickBooksCredentialsIncorrect')]);
        }

        // Prep Data Services
        $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $this->quickBooksClientId,
        'ClientSecret' => $this->quickBooksClientSecret,
        'RedirectURI' => route('quickbooks.callback', company()->hash),
        'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
        'baseUrl' => $this->quickBooksEnvironment
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authorizationUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        return redirect($authorizationUrl);

    }

    public function callback()
    {
        $realmId = request()->realmId;
        $requestCode = request()->code;

        if ($requestCode == '' || $realmId == '') {
            abort(403);
        }

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->quickBooksClientId,
            'ClientSecret' => $this->quickBooksClientSecret,
            'RedirectURI' => route('quickbooks.callback', company()->hash),
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => $this->quickBooksEnvironment
        ));
    
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($requestCode, $realmId);

        $accessTokenValue = $accessToken->getAccessToken();
        $refreshTokenValue = $accessToken->getRefreshToken();

        $this->quickBooksSetting->update(['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue, 'realmid' => $realmId]);

        return redirect(route('invoice-settings.index').'?tab=quickbooks')->with('connect_success', __('messages.quickBooksConnectSuccess'));

    }

    public function createInvoice(ModelsInvoice $invoice)
    {
        $oauth2LoginHelper = new OAuth2LoginHelper($this->quickBooksClientId, $this->quickBooksClientSecret);
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($this->quickBooksRefreshToken);

        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
      
        $this->quickBooksSetting->update(['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue]);

        $this->getCredentials();

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->quickBooksClientId,
            'ClientSecret' => $this->quickBooksClientSecret,
            'accessTokenKey' => $accessTokenValue,
            'refreshTokenKey' => $refreshTokenValue,
            'QBORealmID' => $this->quickBooksRealmId,
            'baseUrl' => $this->quickBooksEnvironment
        ));

        $dataService->setLogLocation('/Users/hlu2/Desktop/newFolderForLog');
        $dataService->throwExceptionOnError(true);

        
        $customerRef = $this->getCustomerObj($dataService, $invoice);

        $itemsData = [];

        foreach ($invoice->items as $item) {
            $itemRef = $this->getItemObj($dataService, $item);

            $itemsData[] = [
                'Amount' => $item->amount,
                'DetailType' => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'ItemRef' => [
                    'value' => $itemRef->Id,
                    'name' => 'Services'
                    ]
                ]
            ];
        }

        $invoiceData = [
            'Line' => $itemsData,
            'CustomerRef' => [
                'value' => $customerRef->Id
            ],
            'CurrencyRef' => [
                'value' => $invoice->currency->currency_code
            ],
        ];

        if ($invoice->client->email) {
            $invoiceData['BillEmail'] = [
                'Address' => $invoice->client->email
            ];
        }
        
        $theResourceObj = Invoice::create($invoiceData);
        $resultingObj = $dataService->Add($theResourceObj);

        // Send quickbooks email to customer
        /** @phpstan-ignore-next-line */
        $dataService->sendEmail($resultingObj, $resultingObj->BillEmail->Address);

        $error = $dataService->getLastError();
        
        if ($error) {
            echo 'The Status code is: ' . $error->getHttpStatusCode() . "\n";
            echo 'The Helper message is: ' . $error->getOAuthHelperError() . "\n";
            echo 'The Response message is: ' . $error->getResponseBody() . "\n";
        
        } else {
            return $resultingObj->Id;
        }

    }

    public function getCustomerObj($dataService, $invoice)
    {

        $customerArray = $dataService->Query("select * from Customer where PrimaryEmailAddr='" . $invoice->client->email . "'");
        $error = $dataService->getLastError();

        if ($error) {
            logger($error);
            
        } else {
            if (is_array($customerArray) && count($customerArray) > 0) {
                return current($customerArray);
            }
        }
    
        $customerDetails = [
            'DisplayName' => $invoice->client->clientDetails->company_name ?? $invoice->client->name,
            'GivenName' => $invoice->client->name,
            'CurrencyRef' => [
                'value' => $invoice->currency->currency_code,
                'name' => $invoice->currency->currency_name
            ]
        ];

        if (!is_null($invoice->client->email)) {
            $customerDetails['PrimaryEmailAddr'] = ['Address' => $invoice->client->email];

        }

        // Create Customer
        $customerRequestObj = Customer::create($customerDetails);
        $customerResponseObj = $dataService->Add($customerRequestObj);
        $error = $dataService->getLastError();
        
        if ($error) {
            logger($error);
        
        } else {
            ClientDetails::where('user_id', $invoice->client_id)->update(['quickbooks_client_id' => $customerResponseObj->Id]);
            return $customerResponseObj;
        }
    }

    public function getItemObj($dataService, $item)
    {
        $itemName = $this->cleanString($item->item_name);
        $itemArray = $dataService->Query("select * from Item WHERE Name='" . $itemName . "'");
        $error = $dataService->getLastError();

        if ($error) {
            logger($error);
        
        } else {
            if (is_array($itemArray) && count($itemArray) > 0) {
                return current($itemArray);
            }
        }

        $incomeAccount = $this->getIncomeAccountObj($dataService);

        // Create Item
        $dateTime = new \DateTime('NOW');
        $ItemObj = Item::create([
            'Name' => $itemName,
            'Description' => $item->item_summary,
            'Active' => true,
            'FullyQualifiedName' => $itemName,
            'Taxable' => !is_null($item->taxes),
            'UnitPrice' => $item->unit_price,
            'Type' => 'Service',
            'PurchaseDesc' => $item->item_summary,
            'PurchaseCost' => $item->unit_price,
            'InvStartDate' => $dateTime,
            'IncomeAccountRef' => [
                'value' => $incomeAccount->Id
            ]
        ]);
        $resultingItemObj = $dataService->Add($ItemObj);

        return $resultingItemObj;  // This needs to be passed in the Invoice creation later
    }

    public function getIncomeAccountObj($dataService)
    {

        $accountArray = $dataService->Query("select * from Account where AccountType='" . self::INCOME_ACCOUNT_TYPE . "' and AccountSubType='" . self::INCOME_ACCOUNT_SUBTYPE . "'");
        $error = $dataService->getLastError();

        if ($error) {
            Logger($error);
        
        } else {
            if (is_array($accountArray) && count($accountArray) > 0) {
                return current($accountArray);
            }
        }
    
        // Create Income Account
        $incomeAccountRequestObj = Account::create([
            'AccountType' => self::INCOME_ACCOUNT_TYPE,
            'AccountSubType' => self::INCOME_ACCOUNT_SUBTYPE,
            'Name' => 'IncomeAccount'
        ]);
        $incomeAccountObject = $dataService->Add($incomeAccountRequestObj);
        $error = $dataService->getLastError();

        if ($error) {
            logger($error);

        } else {
            return $$incomeAccountObject->Id;
        }
    
    }

    public function cleanString($string)
    {
        return preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $string);
    }

    public function createPayment(ModelsPayment $payment)
    {
        $oauth2LoginHelper = new OAuth2LoginHelper($this->quickBooksClientId, $this->quickBooksClientSecret);
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($this->quickBooksRefreshToken);

        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
      
        $this->quickBooksSetting->update(['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue]);

        $this->getCredentials();

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->quickBooksClientId,
            'ClientSecret' => $this->quickBooksClientSecret,
            'accessTokenKey' => $accessTokenValue,
            'refreshTokenKey' => $refreshTokenValue,
            'QBORealmID' => $this->quickBooksRealmId,
            'baseUrl' => $this->quickBooksEnvironment
        ));

        $dataService->setLogLocation('/Users/hlu2/Desktop/newFolderForLog');
        $dataService->throwExceptionOnError(true);

        $paymentObj = Payment::create([
            'CustomerRef' => [
                'value' => $payment->invoice->client->clientDetails->quickbooks_client_id
            ],
            'TotalAmt' => $payment->amount,
            'Line' => [
                'Amount' => $payment->amount,
                'LinkedTxn' => [
                    'TxnId' => $payment->invoice->quickbooks_invoice_id,
                    'TxnType' => 'Invoice'
                ]
            ]
        ]);
        $resultingPaymentObj = $dataService->Add($paymentObj);
        return $resultingPaymentObj->Id;
    }

    public function deletePayment(ModelsPayment $payment)
    {
        $oauth2LoginHelper = new OAuth2LoginHelper($this->quickBooksClientId, $this->quickBooksClientSecret);
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($this->quickBooksRefreshToken);

        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
      
        $this->quickBooksSetting->update(['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue]);

        $this->getCredentials();

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->quickBooksClientId,
            'ClientSecret' => $this->quickBooksClientSecret,
            'accessTokenKey' => $accessTokenValue,
            'refreshTokenKey' => $refreshTokenValue,
            'QBORealmID' => $this->quickBooksRealmId,
            'baseUrl' => $this->quickBooksEnvironment
        ));

        $dataService->setLogLocation('/Users/hlu2/Desktop/newFolderForLog');
        $dataService->throwExceptionOnError(true);

        $theResourceObj = $dataService->FindbyId('payment', $payment->quickbooks_payment_id);
        $dataService->Delete($theResourceObj);

    }

    public function deleteInvoice(ModelsInvoice $invoice)
    {
        $oauth2LoginHelper = new OAuth2LoginHelper($this->quickBooksClientId, $this->quickBooksClientSecret);
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($this->quickBooksRefreshToken);

        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
      
        $this->quickBooksSetting->update(['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue]);

        $this->getCredentials();

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->quickBooksClientId,
            'ClientSecret' => $this->quickBooksClientSecret,
            'accessTokenKey' => $accessTokenValue,
            'refreshTokenKey' => $refreshTokenValue,
            'QBORealmID' => $this->quickBooksRealmId,
            'baseUrl' => $this->quickBooksEnvironment
        ));

        $dataService->setLogLocation('/Users/hlu2/Desktop/newFolderForLog');
        $dataService->throwExceptionOnError(true);

        $theResourceObj = $dataService->FindbyId('invoice', $invoice->quickbooks_invoice_id);
        $dataService->Delete($theResourceObj);

    }

    public function voidInvoice(ModelsInvoice $invoice)
    {
        $oauth2LoginHelper = new OAuth2LoginHelper($this->quickBooksClientId, $this->quickBooksClientSecret);
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($this->quickBooksRefreshToken);

        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
      
        $this->quickBooksSetting->update(['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue]);

        $this->getCredentials();

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->quickBooksClientId,
            'ClientSecret' => $this->quickBooksClientSecret,
            'accessTokenKey' => $accessTokenValue,
            'refreshTokenKey' => $refreshTokenValue,
            'QBORealmID' => $this->quickBooksRealmId,
            'baseUrl' => $this->quickBooksEnvironment
        ));

        $dataService->setLogLocation('/Users/hlu2/Desktop/newFolderForLog');
        $dataService->throwExceptionOnError(true);

        $theResourceObj = $dataService->FindbyId('invoice', $invoice->quickbooks_invoice_id);
        $dataService->void($theResourceObj);

    }

    public function updateInvoice(ModelsInvoice $invoice)
    {
        $oauth2LoginHelper = new OAuth2LoginHelper($this->quickBooksClientId, $this->quickBooksClientSecret);
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($this->quickBooksRefreshToken);

        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
      
        $this->quickBooksSetting->update(['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue]);

        $this->getCredentials();

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->quickBooksClientId,
            'ClientSecret' => $this->quickBooksClientSecret,
            'accessTokenKey' => $accessTokenValue,
            'refreshTokenKey' => $refreshTokenValue,
            'QBORealmID' => $this->quickBooksRealmId,
            'baseUrl' => $this->quickBooksEnvironment
        ));

        $dataService->setLogLocation('/Users/hlu2/Desktop/newFolderForLog');
        $dataService->throwExceptionOnError(true);

        $customerRef = $this->getCustomerObj($dataService, $invoice);

        $itemsData = [];

        foreach ($invoice->items as $item) {
            $itemRef = $this->getItemObj($dataService, $item);

            $itemsData[] = [
                'Amount' => $item->amount,
                'DetailType' => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'ItemRef' => [
                    'value' => $itemRef->Id,
                    'name' => 'Services'
                    ]
                ]
            ];
        }

        $invoiceData = [
            'Line' => $itemsData,
            'CustomerRef' => [
                'value' => $customerRef->Id
            ]
        ];

        if ($invoice->client->email) {
            $invoiceData['BillEmail'] = [
                'Address' => $invoice->client->email
            ];
        }

        $invoiceEntity = $dataService->FindbyId('invoice', $invoice->quickbooks_invoice_id);
        $theResourceObj = Invoice::update($invoiceEntity, $invoiceData);
        $res = $dataService->Update($theResourceObj);
    }

}
