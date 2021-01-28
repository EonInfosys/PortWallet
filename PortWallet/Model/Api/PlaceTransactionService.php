<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Model\Api;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use EonInfosys\PortWallet\Gateway\Command\Form\BuildCommand;

/**
 * Class PlaceTransactionService
 */
class PlaceTransactionService
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var BuildCommand
     */
    private $command;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param BuildCommand $command
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        BuildCommand $command,
        PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->command = $command;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * Place transaction
     *
     * @param int $orderId
     * @return array
     */
    public function placeTransaction($orderId)
    {
        $order = $this->orderRepository->get((int)$orderId);

         return $result = $this->command->execute(
            [
                'payment' => $this->paymentDataObjectFactory->create($order->getPayment())
            ]
        )
            ->get();
    }
}
