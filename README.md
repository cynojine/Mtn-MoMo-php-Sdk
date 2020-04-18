 # Mtn MoMo php Sdk
 Mtn MoMo php Sdk

composer require cynojine/mtn-momo

### Collections is used for requesting a payment from a customer  and checking status of transactions.
- collectionPrimaryKey: Primary Key for the Collection product on the developer portal.
- collectionUserId : For development environment, use the sandbox credentials else use the one on the developer portal.
- collectionApiSecret : For development environment, use the sandbox credentials else use the one on the developer portal.

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
