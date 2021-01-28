<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Sales\Model\Order\Payment;

class AcceptValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDO = SubjectReader::readPayment($validationSubject);
        $payment=$paymentDO->getPayment();
        $isValid = true;
        $fails = [];
        $statements=[];
        if(isset($response['ipn_response'])) {
            $ipnResponseDecode = json_decode($response['ipn_response']);

            if ((isset($ipnResponseDecode->result) && $ipnResponseDecode->result == 'success') && isset($ipnResponseDecode->data->order->status) && $ipnResponseDecode->data->order->status == 'ACCEPTED') {
                /*$result=$ipnResponseDecode->result;
                $order_status=$ipnResponseDecode->data->order->status;
                $transStatus = 'Y';*/
                $isValid = true;
            } elseif ($ipnResponseDecode->result == 'ERROR') {
                $explanation = $ipnResponseDecode->error->explanation;
                $statements = [
                    [
                        false,
                        __('Payment ' . $explanation)
                    ],
                    /*[
                        sprintf('%.2F', $paymentDO->getOrder()->getGrandTotalAmount())
                        === $response['authCost'],
                        __('Amount doesn\'t match.')
                    ],*/
                    [
                        in_array($response['authMode'], ['A', 'E']),
                        __('Not supported response.')
                    ]
                ];
            }
        }
        if($statements) {
            foreach ($statements as $statementResult) {

                if (!$statementResult[0]) {
                    $isValid = false;
                    $fails[] = $statementResult[1];
                }
            }
        }

        return $this->createResult($isValid, $fails);
    }
}
