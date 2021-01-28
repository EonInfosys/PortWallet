<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Model;

/**
 * @api
 * @since 100.0.2
 */
class PortwalletFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new Portwallet model
     *
     * @param array $arguments
     * @return \EonInfosys\PortWallet\Model\Portwallet
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\EonInfosys\PortWallet\Model\Portwallet::class, $arguments);
    }
}
