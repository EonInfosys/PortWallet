<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace EonInfosys\PortWallet\Helper;


use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use EonInfosys\PortWallet\Model\PortwalletFactory;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $postData = null;

    protected $portwalletFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerViewHelper $customerViewHelper,
        PortwalletFactory $portwalletFactory

    ) {
        $this->_customerSession = $customerSession;
        $this->_customerViewHelper = $customerViewHelper;
        $this->portwalletFactory=$portwalletFactory;
        parent::__construct($context);
    }


    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();

        return trim($this->_customerViewHelper->getCustomerName($customer));
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();

        return $customer->getEmail();
    }


    /**
     * Get value  by key
     *
     * @return string
     */
    public function getResponseValue()
    {
        if (null === $this->postData) {
            $this->postData = (array)$this->getDataPersistor()->get('request_data');
            //  $this->getDataPersistor()->clear('request_data');
        }
        if ($this->postData) {
            return $this->postData;
        }
        return '';
    }

    public function clearResponseValue()
    {
        $this->getDataPersistor()->clear('request_data');
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }



    public function sendTransaction($url, $method = 'POST', $body="", $config)
    {

        $authorization = $config['authorization'];

        $headers = array(
            "Authorization:" . $authorization,
            "Content-Type: application/json"
        );


        $curl = curl_init($url);
        // curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if($method=='POST'){
        curl_setopt($curl, CURLOPT_POST, 1);
        }else{
            curl_setopt($curl, CURLOPT_POST, 0);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 45);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        if($body) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curl);


        $info = curl_getinfo($curl);
        $response = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        return $result;
        /*print_r($response);
                exit();*/
        curl_close($curl);
        if (200 === $response || 201==$response) {
            return $result;
        } else {
           /* throw new CommandException('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
          die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            //$errors = curl_error($curl);*/
            return false;
        }

//        return $result;

        /*print_r($response);
        if (200 === $response || 201==$response) {

                return $result;
        } else {
            throw new CommandException('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            //die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            //$errors = curl_error($curl);
            return false;
        }*/
    }

    protected function _parseData($jsonString)
    {
        if (strlen($jsonString) != 0) {
            $data = json_decode($jsonString);
            if ($data->result == 'success' && $data->data->invoice_id != null) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function saveInvoiceDetails($invoice = null)
    {

        $invoice_id = $invoice['invoice'];
        $orderId = $invoice['order_id'];

        $portwalletFactory = $this->portwalletFactory->create();

        $portwallet = $portwalletFactory->getCollection()
            ->addFieldToFilter('invoice', ['eq' => $invoice_id])
            ->addFieldToFilter('order_id', ['eq' => $orderId])
            ->getFirstItem();

        $invoice_entity_id = null;
        if (!empty($portwallet->getData())) {
            $invoice_entity_id = $portwallet->getData('entity_id');
            $portwalletFactory = $portwalletFactory->load($invoice_entity_id);
        }
        if (isset($invoice['order_id'])) {
            $portwalletFactory->setData('order_id', $invoice['order_id']);
        }
        if (isset($invoice['invoice'])) {
            $portwalletFactory->setData('invoice', $invoice['invoice']);
        }
        if (isset($invoice['status'])) {
            $portwalletFactory->setData('status', $invoice['status']);
        }
        if (isset($invoice['invoice_response'])) {
            $portwalletFactory->setData('invoice_response', $invoice['invoice_response']);
        }
        if (isset($invoice['amount'])) {
            $portwalletFactory->setData('amount', $invoice['amount']);
        }
        if (isset($invoice['ipn_response'])) {
            $portwalletFactory->setData('ipn_response', $invoice['ipn_response']);
        }
        $portwalletFactory->save();

        return;
    }

    public function getInvoiceDetails($invoice_id)
    {
        $portwalletFactory = $this->portwalletFactory->create();
        $portwallet = $portwalletFactory->getCollection()
            ->addFieldToFilter('invoice', ['eq' => $invoice_id])
            ->getFirstItem();

        if (!empty($portwallet->getData())) {
            return $portwallet->getData();
        }
        return null;
    }

}
