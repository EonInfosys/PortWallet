<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EonInfosys\PortWallet\Gateway\Command\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Command\CommandException;

/**
 * Class AcceptCommand
 */
class AcceptCommand implements CommandInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @param ValidatorInterface $validator
     * @param HandlerInterface $handler
     */
    public function __construct(
        ValidatorInterface $validator,
        HandlerInterface $handler
    ) {
        $this->validator = $validator;
        $this->handler = $handler;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return string
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);
        $response = SubjectReader::readResponse($commandSubject);


        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $result = $this->validator->validate($commandSubject);

        if (!$result->isValid()) {
            throw new CommandException(
                $result->getFailsDescription()
                ? __(implode(', ', $result->getFailsDescription()))
                : __('Gateway response is not valid.')
            );
        }

        $this->handler->handle(
            $commandSubject,
            SubjectReader::readResponse($commandSubject)
        );
        $payment->getOrder()->setCanSendNewEmailFlag(true);
        switch ($response['authMode']) {
            case 'A':
                $payment->capture();
                break;
            case 'E':
                $payment->authorize(
                    false,
                    $paymentDO->getOrder()->getGrandTotalAmount()
                );
                break;
        }

        $payment->getOrder()->save();
    }
}
