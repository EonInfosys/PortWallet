<?php
/**
 * Created by PhpStorm.
 * User: khasru
 * Date: 11/7/18
 * Time: 12:16 PM
 */

namespace EonInfosys\PortWallet\Model;


class Portwallet extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\EonInfosys\PortWallet\Model\ResourceModel\Portwallet::class);
    }
}
