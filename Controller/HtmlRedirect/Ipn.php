<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Controller\HtmlRedirect;

use Magento\Framework\App\Request;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use EonInfosys\PortWallet\Gateway\Command\ResponseCommand;
use Psr\Log\LoggerInterface;
use EonInfosys\PortWallet\Model\PortwalletFactory;

/**
 * Displays message and redirect to the ResultController with appropriate parameter
 *
 * Class Response
 */
class Ipn extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ResponseCommand
     */
    private $command;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private static $transStatusSuccess = 'Y';

    /**
     * @var string
     */
    private static $transStatusCancel = 'C';

    private static $transStatusFailure = 'F';

    protected $portwalletFactory;

    /**
     * @param Context $context
     * @param ResponseCommand $command
     * @param LoggerInterface $logger
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        ResponseCommand $command,
        LoggerInterface $logger,
        PortwalletFactory $portwalletFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);

        $this->command = $command;
        $this->layoutFactory = $layoutFactory;
        $this->portwalletFactory=$portwalletFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->logger->emergency('ipn_request from Protwallet. Step-1 ');

        $params = $this->getRequest()->getParams();

        $this->logger->emergency(json_encode($params));


        try {
            $this->logger->emergency('ipn_request from Protwallet. Step-2 ');
            if(empty($params)){
                $params['transStatus']="C";
            }elseif (!empty($params['status'])  && $params['status']=='ACCEPTED'){
                $params['transStatus']="Y";
            }
            elseif (!empty($params['status'])  && $params['status']=='REJECTED'){
                $params['transStatus']="C";
            }
            elseif (!empty($params['status'])  && $params['status']=='REJECTED'){
                $params['transStatus']="C";
            }
            else{
                $params['transStatus']="F";
            }
            $params['authMode']='E';
            $params['ipn_request']=true;
            $this->logger->emergency('ipn_request from Protwallet. Step-3 ');
            $this->command->execute(['response' => $params]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->logger->emergency('ipn_request from Protwallet. Step-4 ');
              /*print_r($e->getMessage());
                exit('fail');*/

            return false;
        }
    }
}
