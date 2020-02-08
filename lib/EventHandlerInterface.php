<?php
/**

 */

namespace MTN;


interface EventHandlerInterface
{
    function onInit();

    function onAccessTokenFailure();

    function onPaymentRequestInit($reference_id, $external_id);

    function onPaymentRequestFailure();

    function onPaymentRequestSuccess();

    function onPaymentRequestStatusCheck($reference_id);

    function onPaymentRequestStatusCheckFailure($reference_id);

    function onSuccessful($transaction_data, $env, $reference_id);

    function onFailure($reference_id);

    function onRequery($reference_id);

    function onCancel();

    function onTimeout($reference_id);

    function onReject($reference_id);
}