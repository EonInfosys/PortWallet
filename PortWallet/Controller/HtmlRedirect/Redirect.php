<?php
namespace EonInfosys\PortWallet\Controller\HtmlRedirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use EonInfosys\PortWallet\Model\Api\PlaceTransactionService;


class Redirect extends Action
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
        Session $checkoutSession,
        DataPersistorInterface $dataPersistor,
        Order $orderObject,
        PageFactory $resultPageFactory,
        UrlInterface $urlInterface
    ) {
        $this->placeTransactionService = $placeTransactionService;
        $this->session = $checkoutSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        $this->orderObj=$orderObject;
        $this->urlInterface=$urlInterface;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $orderId = $this->session->getData('last_order_id');

        $quote_id = "";
        if (isset($_REQUEST['quote_id']) && !empty($_REQUEST['quote_id'])) {
            $quote_id = $_REQUEST['quote_id'];
            $currentUrl = $this->urlInterface->getCurrentUrl();
            $redirectUrl = $this->urlInterface->getUrl('portwallet/htmlredirect/redirect');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
            /*$order = $this->orderObj->loadByAttribute('quote_id', $quote_id);
            echo  $orderId = $order->getId();*/
        }

        if (!is_numeric($orderId)) {
            /*    $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
                return $resultJson->setData(['message' => __('No such order id.')]);*/
            echo __('No such order id.');
            return false;
        }

        $response = $this->placeTransactionService->placeTransaction($orderId);
        $response['order_id']=$orderId;
        $this->dataPersistor->set('request_data', $response);


        if(isset($response['result']) && $response['result']=='success'){
            $url=$response['redirect_url'] ;
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($url);
            return $resultRedirect;
        }else {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $block = $resultPage->getLayout()
                ->createBlock('EonInfosys\PortWallet\Block\Redirect')
                ->toHtml();
            $this->getResponse()->setBody($block);
            // return $resultPage;
        }
    }
}