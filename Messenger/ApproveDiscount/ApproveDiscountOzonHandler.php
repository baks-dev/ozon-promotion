<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Promotion\Messenger\ApproveDiscount;


use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Ozon\Promotion\Api\Discounts\UpdateOzonApproveDiscountRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class ApproveDiscountOzonHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private UpdateOzonApproveDiscountRequest $updateOzonApproveDiscountRequest,
        private DeduplicatorInterface $deduplicator,
        LoggerInterface $ozonPromotionLogger
    )
    {
        $this->logger = $ozonPromotionLogger;
    }

    /**
     * Метод рассчитывает от минимальной цены максимально допустимый размер скидки
     */
    public function __invoke(ApproveDiscountOzonMessage $message): void
    {
        $Deduplicator = $this->deduplicator
            ->namespace('ozon-promotion')
            ->deduplication([$message->getId(), self::class]);

        if($Deduplicator->isExecuted())
        {
            return;
        }

        /**
         * Округляем запрашиваемую цену до десятых в пользу клиента
         */
        $requested = $message->getDiscount() - 50;
        $price = round($requested, -1);

        $approve = $this->updateOzonApproveDiscountRequest
            ->profile($message->getProfile())
            ->identifier($message->getId())
            ->quantity($message->getQuantity())
            ->price($price)
            ->approve();

        $Deduplicator->save();

        if($approve)
        {
            $this->logger->info(
                sprintf('Согласованная цена заявке %s => %s', $message->getId(), $price),
                ['profile' => (string) $message->getProfile()]
            );

            return;
        }

        $this->logger->critical(
            sprintf('Ошибка при согласовании заявки %s => %s', $message->getId(), $price),
            ['profile' => (string) $message->getProfile()]
        );

    }
}
