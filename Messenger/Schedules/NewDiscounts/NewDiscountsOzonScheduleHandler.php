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

namespace BaksDev\Ozon\Promotion\Messenger\Schedules\NewDiscounts;

use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Promotion\Api\Discounts\New\GetOzonDiscountsRequest;
use BaksDev\Ozon\Promotion\Api\Discounts\New\OzonDiscountDTO;
use BaksDev\Ozon\Promotion\Messenger\Schedules\ApproveDiscount\ApproveDiscountOzonMessage;
use DateInterval;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class NewDiscountsOzonScheduleHandler
{
    public function __construct(
        private GetOzonDiscountsRequest $getOzonDiscountsRequest,
        private MessageDispatchInterface $messageDispatch,
        private DeduplicatorInterface $deduplicator
    ) {}

    /**
     * Метод получает все заявки на скидку, и создает сообщение на одобрение
     */
    public function __invoke(NewDiscountsOzonScheduleMessage $message): void
    {
        $Deduplicator = $this->deduplicator
            ->expiresAfter(DateInterval::createFromDateString('5 seconds'))
            ->namespace('ozon-promotion')
            ->deduplication([$message, self::class]);

        if($Deduplicator->isExecuted())
        {
            return;
        }

        $Deduplicator->save();

        $discounts = $this->getOzonDiscountsRequest
            ->profile($message->getProfile())
            ->findAll();

        if($discounts->valid() === false)
        {
            return;
        }

        /** @var OzonDiscountDTO $OzonDiscountDTO */
        foreach($discounts as $OzonDiscountDTO)
        {
            $ApproveDiscountOzonMessage = new ApproveDiscountOzonMessage(
                $message->getProfile(),
                $OzonDiscountDTO->getId(),
                $OzonDiscountDTO->getMinPrice(),
                $OzonDiscountDTO->getQuantity()
            );

            $this->messageDispatch->dispatch(
                message: $ApproveDiscountOzonMessage,
                transport: (string) $message->getProfile()
            );
        }
    }
}
