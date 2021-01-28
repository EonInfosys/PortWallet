<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

    /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        //$payment->setTransactionId($response['invoice']);
        /*$invoice=$status=$invoice_id="";
        if ($response && $payment) {
            $invoice = $response['invoice'];
            $status = $response['status'];
            $response['invoice']=$invoice;
            $response['status']=$status;
        }*/
        $payment->setLastTransId($response['invoice']);
        $payment->setAdditionalInformation('response', $response);
        $payment->setIsTransactionClosed(false);
    }
}
