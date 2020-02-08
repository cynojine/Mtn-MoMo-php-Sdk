<?php
/**

 */
namespace MTN;

require_once( MOMOPAY_PLUGIN_DIR_PATH . 'mtn-momopay-php-sdk/vendor/autoload.php' );

use GuzzleHttp\Client;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class Momopay
{
    protected $primary_key;
    protected $secondary_key;
    protected $api_user;
    protected $api_key;
    protected $env;
    protected $base_url;

    protected $access_token = null;

    protected $reference_id;
    protected $amount;
    protected $currency;
    protected $external_id;
    protected $phone;
    protected $payer_message;
    protected $payee_note;
    protected $body;
    protected $headers;
    protected $requery_count = 0;
    protected $error;
    protected $handler;
    public $logger;


    /**
     * Momopay constructor.
     * @param $primary_key
     * @param $api_user
     * @param $api_key
     * @param $base_url
     * @param $env
     * @param $event_handler
     */
    public function __construct($primary_key, $api_user, $api_key, $base_url, $env, $event_handler)
    {
        $this->primary_key = $primary_key;
        $this->api_user = $api_user;
        $this->api_key = $api_key;
        $this->base_url = $base_url;
        $this->env = $env;

        // create a log channel
        $log = new Logger('mtn/momopay');
        $this->logger = $log;
        $log->pushHandler(new RotatingFileHandler('momopay.log', 90, Logger::DEBUG));

        // logs
        $this->logger->notice('Momopay Class Initializes....');

        $this->setEventHandler($event_handler);
        $this->setAccessToken();

        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
    public function setExternalID($external_id)
    {
        $this->external_id = $external_id;
        return $this;
    }

    public function setPayerMessage($message)
    {
        $this->payer_message = $message;
        return $this;
    }
    public function setPayeeNote($note)
    {
        $this->payee_note = $note;
        return $this;
    }

    protected function setReferenceId()
    {
        $this->reference_id = self::generateUuid();
    }

    public function setBody()
    {
        $data =  array(
            'amount' => $this->amount,
            'currency' => $this->currency,
            'externalId' => $this->external_id,
            'payer' => array(
                'partyIdType' => 'MSISDN',
                'partyId' => $this->phone
            ),
            'payerMessage' => $this->payer_message,
            'payeeNote' => $this->payee_note
        );
        $this->body = json_encode($data);
    }

    /**
     * Sets the event hooks for all available triggers
     * @param object $handler This is a class that implements the Event Handler Interface
     * @return object
     * */
    public function setEventHandler($handler){
        $this->handler = $handler;
        return $this;
    }

    protected function setAccessToken()
    {
        $url = $this->base_url . 'token/';
        $this->setHeaders(array(
            'Authorization' => 'Basic ' . base64_encode( $this->api_user . ':' . $this->api_key ),
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $this->primary_key
        ));

        $this->logger->notice('Requesting for MomoPay API Access Token');

        try {
            //$response = Request::post($url, $this->headers);
            $client = new  Client();
            $response = $client->post($url, array(
                'headers' =>$this->headers,
            ));
        } catch (\Exception $e) {
            $this->logger->error('Error Fetching MomoPay API Access Token: '.$e->getMessage());
            $this->handler->onAccessTokenFailure();
        }

        if (isset($response)){
            $body = json_decode($response->getBody()->getContents());
            $this->access_token =  'Bearer ' . $body->access_token;

            $this->logger->notice('Successfully Fetched MomoPay API Access Token');
        }
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($reason)
    {
        switch ($reason) {
            case 'EXPIRED':
                $this->error = "The Payment Request Expired! Please Try Again! ($reason)";
                $this->logger->warn('Payment request expired');
                $this->handler->onTimeout($this->reference_id);
                break;
            case 'INTERNAL_PROCESSING_ERROR':
                $this->error = "The Payment Request was Rejected! Please Try Again! ($reason)";
                $this->logger->warn('Payment request rejected possibly due to lack of enough funds by payer');
                $this->handler->onReject($this->reference_id);
                break;
            case 'APPROVAL_REJECTED':
                $this->error = "The Payment Request Was Cancelled by phone owner! Please Try Again ($reason)";
                $this->logger->warn('Payment request Cancelled by Payer');
                $this->handler->onCancel($this->reference_id);
                break;
            default:
                $this->error = "Payment Error: Please try again later! ($reason)";
        }
    }

    protected function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    public function getHeaders()
    {
        return $this->headers;
    }

    protected function getFormattedHeaders($headers)
    {
        $formatted = array();
        foreach ($headers as $key => $value){
            $formatted[] = $key.': '.$value;
        }
        return $formatted;
    }

    public function requestPayment()
    {
        $this->setReferenceId();
        $this->setBody();

        $url = $this->base_url . 'v1_0/requesttopay';
        $this->setHeaders(array(
            'Authorization' => $this->access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $this->primary_key,
            'X-Reference-Id' => $this->reference_id,
            'X-Target-Environment' => $this->env
        ));

        $this->logger->notice('Initializing Payment Request on MomoPay');
        $this->handler->onPaymentRequestInit($this->reference_id, $this->external_id);

        try {
            $client = new  Client();
            $response = $client->post($url, array(
                'headers' => $this->headers,
                'body' => $this->body
            ));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if (isset($response) && in_array($response->getStatusCode(), array(200, 201, 202))){ //202 usually expected.
            $this->logger->notice('Successfully sent Payment Request on MomoPay');
            $this->handler->onPaymentRequestSuccess();
            return true;
        }

        $this->logger->warn('Error Sending MomoPay Payment Request: '.(isset($error) ? $error : ''));
        $this->handler->onPaymentRequestFailure();

        return false;
    }

    public function getRequestStatus()
    {
        $url = $this->base_url . 'v1_0/requesttopay/'.$this->reference_id;

        if ($this->requery_count === 0){
            $this->logger->notice('Fetching Payment Request Status on MomoPay');
            $this->handler->onPaymentRequestStatusCheck($this->reference_id);
        } else {
            $this->logger->notice('Requerying Payment Request Status on MomoPay');
        }

        try {
            $client = new  Client();
            $response = $client->get($url, array(
                'headers' => array_diff_key($this->headers, array('X-Reference-Id' => $this->reference_id)),
            ));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $status = 'error';
        if (isset($response)){
            $body = json_decode($response->getBody()->getContents());

            switch ($body->status) {
                case 'SUCCESSFUL':
                    $status = 'successful';
                    $this->logger->notice('Payment Successful on MomoPay');
                    $this->handler->onSuccessful($this->body, $this->env, $this->reference_id);
                    break;
                case 'FAILED':
                    $status = 'failed';
                    $this->logger->warn('Payment Failed on MomoPay');
                    $this->handler->onFailure($this->reference_id);
                    $this->setError($body->reason);
                    break;
                case 'PENDING':
                    if ($this->requery_count < 6){
                        $status = $this->requeryRequestStatue();
                    }else{
                        $status = 'failed';
                    }
                    break;
                default:
                    $status = 'failed';
            }
        }

        if (isset($error)){
            $this->logger->error('Error Checking for MomoPay Payment Request Status: '.$error);
            $this->handler->onPaymentRequestStatusCheckFailure($this->reference_id);
        }

        return $status;
    }

    protected function requeryRequestStatue()
    {
        if ($this->requery_count === 0){
            $this->logger->notice('Requerying transaction on momopay');
            $this->handler->onRequery($this->reference_id);
        }
        $this->requery_count ++;
        sleep(10);
        return $this->getRequestStatus();
    }

    /**
     * uuid4 generator
     * @return string uuid4
     * @throws \Exception
     */
    public static function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}