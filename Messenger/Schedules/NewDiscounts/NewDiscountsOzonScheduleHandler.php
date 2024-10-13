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

use BaksDev\Ozon\Promotion\Api\Discounts\New\GetOzonDiscountsRequest;
use BaksDev\Ozon\Promotion\Api\Discounts\New\OzonDiscountDTO;
use BaksDev\Ozon\Promotion\Api\Discounts\UpdateOzonApproveDiscountRequest;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class NewDiscountsOzonScheduleHandler
{
    public function __construct(
        private GetOzonDiscountsRequest $getOzonDiscountsRequest,
        private UpdateOzonApproveDiscountRequest $updateOzonApproveDiscountRequest
    ) {}

    public function __invoke(NewDiscountsOzonScheduleMessage $message): void
    {

        $discounts = $this->getOzonDiscountsRequest
            ->profile($message->getProfile())
            ->findAll();

        if($discounts->valid() === false)
        {
            return;
        }

        /** @var OzonDiscountDTO $discount */
        foreach($discounts as $discount)
        {
            /**
             * Делаем Approve на указанную стоимость
             * - если запрашиваемая цена больше минимальной - согласовываем запрашиваемую
             * - если запрашиваемая цена меньше чем минимально допустимая - согласовываем минимальную
             */
            $price = $discount->isApproved() === true ? $discount->getRequested() : $discount->getMinPrice();

            $this->updateOzonApproveDiscountRequest
                ->profile($message->getProfile())
                ->identifier($discount->getId())
                ->price($price)
                ->approve();
        }


        /** Получаем все скидки */ // studded  spiky

        /** Если скидка до 5% - сохраняем */

        /** Если скидка свыше 5% - отклоняем */

    }
}
