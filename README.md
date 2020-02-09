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

 $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->go_live = 'yes' === $this->get_option( 'go_live' );

            $this->primary_key   = $this->get_option( 'primary_key' );
            $this->secondary_key   = $this->get_option( 'secondary_key' );

            $this->api_user   =  $this->go_live ? $this->get_option( 'live_api_user' ) : $this->get_option( 'test_api_user' );
            $this->api_key   =  $this->go_live ? $this->get_option( 'live_api_key' ) : $this->get_option( 'test_api_key' );
            $this->env = $this->go_live ? 'live' : 'sandbox';
            $this->base_url = $this->go_live ? 'https://live.momodeveloper.mtn.com/collection/' : 'https://sandbox.momodeveloper.mtn.com/collection/';
            $this->currency = $this->get_option( 'currency' );
