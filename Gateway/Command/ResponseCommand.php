<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use EonInfosys\PortWallet\Gateway\Validator\DecisionValidator;
use EonInfosys\PortWallet\Helper\Data;
use EonInfosys\PortWallet\Model\PortwalletFactory;

/**
 * Class ResponseCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResponseCommand implements CommandInterface
{
    const ACCEPT_COMMAND = 'accept_command';

    const CANCEL_COMMAND = 'cancel_command';

    /**
     * Transaction result codes map onto commands
     *
     * @var array
     */
    static private $commandsMap = [
        'C' => self::CANCEL_COMMAND,
        'Y' => self::ACCEPT_COMMAND
    ];

    protected $invoice_test = 'https://api-sandbox.portwallet.com/payment/v2/invoice/';
    protected $invoice = 'https://api.portwallet.com/payment/v2/invoice/';

    //https://api-sandbox.portwallet.com/payment/v2/invoice/ipn/85BC30171EA29531/3394.00

    protected $ipn_test = 'https://api-sandbox.portwallet.com/payment/v2/invoice/ipn/';
    protected $ipn = 'https://api.portwallet.com/payment/v2/invoice/ipn/';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var Logger
     */
    private $logger;

    protected $portwalletHelper;

    /**
     * @var OrderSender
     */
    private $orderSender;


    /**
     * @var ConfigInterface
     */
    private $config;

    protected $portwalletFactory;


    /**
     * @param CommandPoolInterface $commandPool
     * @param ValidatorInterface $validator
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param Logger $logger
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        ValidatorInterface $validator,
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        Logger $logger,
        OrderSender $orderSender,
        Data $portwalletHelper,
        PortwalletFactory $portwalletFactory,
        \Webkul\Odoomagentoconnect\Observer\SalesOrderDiscountUpdateObserver 
        $salesOrderDiscountUpdateObserver,
        ConfigInterface $config
    ) {
        $this->commandPool = $commandPool;
        $this->validator = $validator;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->logger = $logger;
        $this->portwalletHelper = $portwalletHelper;
        $this->orderSender = $orderSender;
        $this->config = $config;
        $this->portwalletFactory=$portwalletFactory;
        $this->salesOrderDiscountUpdateObserver=$salesOrderDiscountUpdateObserver;
    }

    /**
     * @param array $commandSubject
     *
     * @return void
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $this->logger->debug($commandSubject);
        $transStatus = null;
        $ipn_request = false;
        $order=null;
        $orderId = null;

        $response = SubjectReader::readResponse($commandSubject);

        if (isset($response['status']) && $response['status'] == 'ACCEPTED') {
            $transStatus = 'Y';
        } elseif (isset($response['status']) && $response['status'] == 'REJECTED') {
            $transStatus = 'C';
        }else{
            $transStatus = 'C';
        }
        $invoice = isset($response['invoice'])?$response['invoice']:"";
        $amount = isset($response['amount'])?$response['amount']:"";


        if (isset($response['ipn_request']) && $response['ipn_request']) {
            $ipn_request = true;

            $storeId=1;
            $config = $this->getConfigaration($storeId);
            $ipnUrl = $this->getIpnUrl($config);
            $ipnUrl = $ipnUrl . $invoice . '/' . $amount;

            $ipnResponse = $this->portwalletHelper->sendTransaction($ipnUrl, 'GET', array(), $config);
            $response['ipn_response'] = $ipnResponse;
            $ipnResponseDecode = json_decode($ipnResponse);

            if ((isset($ipnResponseDecode->result) && $ipnResponseDecode->result == 'success') && isset($ipnResponseDecode->data->order->status) && $ipnResponseDecode->data->order->status == 'ACCEPTED') {
                /*$result=$ipnResponseDecode->result;
                $order_status=$ipnResponseDecode->data->order->status;*/
                $orderId=$ipnResponseDecode->data->reference;
                $transStatus = 'Y';
            } elseif ((isset($ipnResponseDecode->result) && $ipnResponseDecode->result == 'success') && isset($ipnResponseDecode->data->order->status)) {
                /*$result=$ipnResponseDecode->result;
                $order_status=$ipnResponseDecode->data->order->status;*/
                $orderId=$ipnResponseDecode->data->reference;
                //$transStatus = 'Y';
            } else {
                $_data = $this->portwalletHelper->getInvoiceDetails($invoice);
                $orderId = null;
                if (isset($_data['order_id']) && $_data['order_id'] != null) {
                    $orderId = $_data['order_id'];
                }
            }

            if ($orderId) {
                //$order = $this->orderRepository->get((int)$orderId);
                $order=  $this->order->loadByIncrementId($orderId);

                $_invoiceData['status'] = $ipnResponseDecode->data->order->status;
                $_invoiceData['order_id'] = $orderId;
                $_invoiceData['amount'] = $amount;
                $_invoiceData['invoice'] = $invoice;
                $_invoiceData['ipn_response'] = $ipnResponse;
                $this->portwalletHelper->saveInvoiceDetails($_invoiceData);

                if (isset($ipnResponseDecode->data->order->discount)) {
                    $this->updateOrderDiscount($order, $ipnResponseDecode);
                }
            }
        }

        $requestData = $this->portwalletHelper->getResponseValue();

        if (!empty($requestData) && isset($requestData['order_id'])) {
            $_orderId = $requestData['order_id'];
            $order = $this->orderRepository->get((int)$_orderId);
            $orderId= $order->getIncrementId();
            $storeId = $order->getStoreId();
            $config = $this->getConfigaration($storeId);

          //  $invoice = $response['invoice'];
            $invoiceUrl = $this->getInvoiceUrl($config);
            $invoiceUrl = $invoiceUrl . $invoice;

            $invoiceResponse = $this->portwalletHelper->sendTransaction($invoiceUrl, 'GET', array(), $config);
         //   if(!$invoiceResponse) return false;
            $response['invoice_data'] = $invoiceResponse;

            $invoiceResponseData = json_decode($invoiceResponse);
            //$transaction_id=$invoiceResponseData->data->billing->gateway->transaction_id;
            //$transactions=$invoiceResponseData->data->transactions;
            //$invoiceAmount=(double)$transactions[0]->amount;
            /*echo "<pre>";
             print_r($invoiceResponseData);
             exit();*/
            if ((isset($invoiceResponseData->result) && $invoiceResponseData->result == 'success') && isset($invoiceResponseData->data->order->status) && $invoiceResponseData->data->order->status == 'ACCEPTED') {
                $transStatus = 'Y';
            }

            if (isset($invoiceResponseData->data->order->discount)) {
                $this->updateOrderDiscount($order, $invoiceResponseData);
            }

        }

        /*$result = $this->validator->validate($commandSubject);
        if (!$result->isValid()) {
            $transStatus = 'C';
        }*/

        if ($orderId) {
            #$order = $this->orderRepository->get((int)$orderId);
            $order=  $this->order->loadByIncrementId($orderId);
            $actionCommandSubject = [
                'response' => $response,
                'payment' => $this->paymentDataObjectFactory->create(
                    $order->getPayment()
                )
            ];
            if ($transStatus) {
                $command = $this->commandPool->get(
                    self::$commandsMap[$transStatus]
                );
                $command->execute($actionCommandSubject);

                if ($transStatus == 'Y' && $ipn_request) {
                    $this->orderSender->send($order);
                }
            }
        }
    }

    private function getConfigaration($storeId)
    {
        $config = array();

        $app_key = $this->config->getValue('app_key', $storeId);
        $secret_key = $this->config->getValue('secret_key', $storeId);
        $debug = $this->config->getValue('debug', $storeId);
        $test = $this->config->getValue('sandbox_flag', $storeId);
        $time=time();

        if ($app_key && $secret_key) {
            $key=$secret_key.$time;
            $config['app_key'] = $app_key;
            $config['secret_key'] =  $secret_key;
            $config['timestamp'] =  $time;
            $config['token'] = md5($key);
            $config['debug'] = $debug;
            $config['test'] = $test;
            $config['authorization'] = "Bearer ".base64_encode($app_key.':'.md5($secret_key.time()));
        }
        return $config;
    }

    protected function updateOrderDiscount($order, $invoiceResponseData)
    {
        if (isset($invoiceResponseData->data->order->discount->amount)) {
            $custDiscount = $invoiceResponseData->data->order->discount->amount;
            $custDiscountName = $invoiceResponseData->data->order->discount->name;
            $totalAmount = $invoiceResponseData->data->order->amount;


            $discount_description = $order->getDiscountDescription();
            if ($discount_description) {
                $discount_description .= ',';
            }
            $discount_description .= $custDiscountName;

            $base_discount_amount = $order->getBaseDiscountAmount();
            $discount_amount = $order->getDiscountAmount();

            $_discountBase = $base_discount_amount + $custDiscount;
            $_discount = $discount_amount + $custDiscount;


            $grandTotal = $order->getGrandTotal();
            $baseGrandTotal = $order->getBaseGrandTotal();
            $subTotal = $order->getSubTotal();

            if ($totalAmount !== $grandTotal) {
                $grandTotal = $totalAmount;
                $baseGrandTotal = $totalAmount;
            }

            $grandTotal = ($grandTotal - $custDiscount);
            $baseGrandTotal = ($baseGrandTotal - $custDiscount);


            $order->setBaseDiscountAmount($_discountBase);
            $order->setDiscountAmount($_discount);
            $order->setDiscountDescription($discount_description);

            $order->setGrandTotal($grandTotal);
            $order->setBaseGrandTotal($baseGrandTotal);
            $order->save();

            $this->salesOrderDiscountUpdateObserver->syncOrderDiscount($order);
        }

    }

    public function getInvoiceUrl($config)
    {

        if ($config['test']) {
            return $this->invoice_test;
        } else {
            return $this->invoice;
        }
    }
    public function getIpnUrl($config)
    {

        if ($config['test']) {
            return $this->ipn_test;
        } else {
            return $this->ipn;
        }
    }


}
