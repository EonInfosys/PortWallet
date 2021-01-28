<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Api;

/**
 * Interface GuestPaymentInformationManagementProxyInterface
 * @api
 * @since 100.1.0
 */
interface IpnInterface
{
    /**
     * Save online payment information
     *
     * @param string $status
     * @param string $invoice
     * @param string $amount
     *
     *
     * @return boolean
     */
    public function savePaymentInformation($status,$invoice,$amount);
}
