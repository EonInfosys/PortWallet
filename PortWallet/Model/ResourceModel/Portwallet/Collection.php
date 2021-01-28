<?php

namespace EonInfosys\PortWallet\Model\ResourceModel\Portwallet;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct() {
        $this->_init('EonInfosys\PortWallet\Model\Portwallet', 'EonInfosys\PortWallet\Model\ResourceModel\Portwallet');
        $this->addOrder('entity_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
    }
}
