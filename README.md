 # Mtn MoMo php Sdk
 Mtn MoMo php Sdk

```cmd
composer require cynojine/mtn-momo



<?php
define( 'MOMOPAY_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
require_once( MOMOPAY_PLUGIN_DIR_PATH . 'mtn-momopay-php-sdk/lib/Momopay.php' );
require_once( MOMOPAY_PLUGIN_DIR_PATH . 'mtn-momopay-php-sdk/lib/EventHandlerInterface.php' );
require_once( MOMOPAY_PLUGIN_DIR_PATH . 'mtn-momopay-php-sdk/lib/MomopayEventHandler.php' );

use MTN\MomopayEventHandler;
use MTN\Momopay;
