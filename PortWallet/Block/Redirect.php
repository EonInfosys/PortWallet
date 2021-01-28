<?php
namespace EonInfosys\PortWallet\Block;

use Magento\Framework\Controller\ResultFactory;


class Redirect extends \Magento\Framework\View\Element\Template
{

    protected $_logo;
    protected $_portwalletHelper;
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var array
     */
    private $postData = null;

    protected $resultFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \EonInfosys\PortWallet\Helper\Data $portwalletHelper,
        ResultFactory $resultFactory,
        array $data = []
    )
    {
        $this->_logo = $logo;
        $this->_portwalletHelper = $portwalletHelper;
        $this->resultFactory=$resultFactory;
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        $requestData=$this->_portwalletHelper->getResponseValue();

        $logoSrc=$this->getLogoSrc();
        if(isset($requestData['result']) && $requestData['result']=='success'){

           echo $url=$requestData['redirect_url'] ;


            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($url);
            return $resultRedirect;

            $html = '<html><body>';
            $html .= '<h1 style="padding: 15px; vertical-align: middle; display: flex; justify-content: center; align-items: center; font-size: 14px;">
                            <span style="padding-right:10px;">You will be redirected to the payment gateway in a few seconds</span><img src="https://www.portwallet.com/en_US/images/portwallet.svg" alt="portwallet Checkout" />
                        </h1>';
           // $html .= '<script type="text/javascript"> window.location.href = '. $url.'; </script>';
            $html .= '</body></html>';
            return $html;
        }else{
            $html = '<html><body>';
            $html .= '<h1 style="padding: 15px; vertical-align: middle; display: flex; justify-content: center; align-items: center;"><a href="'. $this->geturl().'"><img src="'. $logoSrc.'" alt="logo" /></a> </h1>';
            $html .= '<h2 style="padding: 15px; vertical-align: middle; display: flex; justify-content: center; align-items: center;"><span style="padding-right:10px;">Get-way error! Please try again letter.</span> </h2>    ';
            if(isset($requestData['description'])) {
                $html .= '<h3 style="padding: 15px; vertical-align: middle; display: flex; justify-content: center; align-items: center;"><span style="padding-right:10px;">' . $requestData['description'] . '</span> </h3>    ';
            }
            $html .= '</body></html>';
            return $html;
        }
     //   print_r($requestData);
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        $logosrc = $this->_logo->getLogoSrc();

        if (strpos($logosrc, 'https') === false) {
            $logosrc = str_replace('http', 'https', $this->_logo->getLogoSrc());
        }
        return $logosrc;
    }
}
