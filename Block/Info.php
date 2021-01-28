<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Block;

use Magento\Framework\Phrase;


class Info extends \Magento\Payment\Block\ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return parent::getLabel($field);
    }

    /**
     * Returns value view
     *
     * @param string $field
     * @param string $value
     * @return string | Phrase
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getValueView($field, $value)
    {
        return parent::getValueView($field, $value);
    }

    /**
     * Prepare PortWallet-specific payment information
     *
     * @param \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);


        $payment = $this->getInfo();
        //$order = $payment->getOrder();
        //print_r($payment->getLastTransId());
        $_additionalInfo = $payment->getAdditionalInformation();
        $info = array();
        $invoice = $status = $amount = $network = $networkStatus = $explanation = null;
        /*echo "<pre>";
        print_r(json_decode($_additionalInfo['request']));
        echo "</pre>"*/;
        if (isset($_additionalInfo['response'])) {

            $invoice = isset($_additionalInfo['response']['invoice']) ? $_additionalInfo['response']['invoice'] : "";
            $status = isset($_additionalInfo['response']['status']) ? $_additionalInfo['response']['status'] : "";
            $amount = isset($_additionalInfo['response']['amount']) ? $_additionalInfo['response']['amount'] : "";

            if (isset($_additionalInfo['response']['ipn_response'])) {
                $invoice_data = json_decode($_additionalInfo['response']['ipn_response']);
                if ($invoice_data->result == 'success') {
                    $network = $invoice_data->data->billing->gateway->network;
                    $networkStatus = $invoice_data->data->billing->gateway->status;
                } elseif ($invoice_data->result == 'ERROR') {
                    $explanation = $invoice_data->error->explanation;
                }
            }
            if ($invoice) {
                $info['Invoice Id'] = $invoice;
            }
            if ($status && $amount && $network && $networkStatus) {
                $info['Payment status'] = $status . ', Amount : ' . $amount . ', ' . $network;
            } elseif($explanation) {
                $info['Payment status'] = $networkStatus . ', Amount : ' . $amount . ', ' . $network . ', Explanation' . $explanation;
            }

        } elseif (isset($_additionalInfo['request'])) {
            $request = json_decode($_additionalInfo['request']);
            if ($request->result == 'success') {
                $invoice = $request->data->invoice_id;
                $invoiceStatus = $request->data->order->status;
                $invoiceAmount = $request->data->order->amount;

                $info['Invoice Id'] = $invoice;
                $info['Payment status'] = $invoiceStatus . ', Amount : ' . $invoiceAmount;

            } elseif ($request->result == 'ERROR' && $request->error->explanation) {
                $info['Payment status'] = $request->error->explanation;

            }

        }

        return $transport->addData($info);
    }
}
