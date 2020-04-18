 # Mtn MoMo php Sdk
 Mtn MoMo php Sdk

composer require cynojine/mtn-momo


```php
<?php
require_once( MOMOPAY_PLUGIN_DIR_PATH . 'mtn-momopay-php-sdk/lib/Momopay.php' );
require_once( MOMOPAY_PLUGIN_DIR_PATH . 'mtn-momopay-php-sdk/lib/EventHandlerInterface.php' );
require_once( MOMOPAY_PLUGIN_DIR_PATH . 'mtn-momopay-php-sdk/lib/MomopayEventHandler.php' );

use MTN\MomopayEventHandler;
use MTN\Momopay;

$config = new Momopayconfig([ 
    // mandatory credentials
    'baseUrl'               => 'https://sandbox.momodeveloper.mtn.com', 
    'currency'              => 'EUR', 
    'targetEnvironment'     => 'sandbox', 

    // collection credentials
    "collectionApiSecret"   => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
    "collectionPrimaryKey"  => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
    "collectionUserId"      => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
]);


$collection = new MtnCollection($config); 

$params = [
    "mobileNumber"      => '0964686107', 
    "amount"            => '100', 
    "externalId"        => '554000302',
    "payerMessage"      => 'some note',
    "payeeNote"         => '1212'
];

$transactionId = $collection->requestToPay($params);

$transaction = $collection->getTransaction($transactionId);
