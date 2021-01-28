<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Controller\HtmlRedirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Sales\Model\Order;
use EonInfosys\PortWallet\Model\Api\PlaceTransactionService;

/**
 * Class GetTransactionData
 * @package EonInfosys\PortWallet\Controller\HtmlRedirect
 */
class GetTransactionData extends Action
{
    /**
     * @var PlaceTransactionService
     */
    private $placeTransactionService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PlaceTransactionService $placeTransactionService
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        PlaceTransactionService $placeTransactionService,
        DataPersistorInterface $dataPersistor,
        Order $orderObject,
        Session $checkoutSession
    ) {
        $this->placeTransactionService = $placeTransactionService;
        $this->session = $checkoutSession;
        $this->dataPersistor = $dataPersistor;
        $this->orderObj=$orderObject;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $__orderId = $this->session->getData('last_order_id');
        $quote_id = "";
        if (isset($_REQUEST['quote_id']) && !empty($_REQUEST['quote_id'])) {
            $quote_id = $_REQUEST['quote_id'];
        }

        $order = $this->orderObj->loadByAttribute('quote_id', $quote_id);
      echo  $orderId = $order->getId();


        if (!is_numeric($orderId)) {
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
            return $resultJson->setData(['message' => __('No such order id.')]);
        }

        $response = $this->placeTransactionService->placeTransaction($orderId);
        echo $response['redirect_url'];
        $this->dataPersistor->set('request_data', $response);
        $resultJson->setData('redirect_url', $response['redirect_url']);
        return $resultJson;
    }
}
