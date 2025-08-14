<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Promotion\Api\Discounts\New\GetOzonDiscountsRequest;
use BaksDev\Ozon\Promotion\Api\Discounts\New\OzonDiscountDTO;
use BaksDev\Ozon\Promotion\Messenger\ApproveDiscount\ApproveDiscountOzonMessage;
use BaksDev\Ozon\Promotion\Messenger\RejectDiscount\RejectDiscountOzonMessage;
use BaksDev\Ozon\Repository\OzonTokensByProfile\OzonTokensByProfileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class NewDiscountsOzonScheduleHandler
{
    public function __construct(
        private GetOzonDiscountsRequest $getOzonDiscountsRequest,
        private MessageDispatchInterface $messageDispatch,
        private OzonTokensByProfileInterface $OzonTokensByProfile
    ) {}

    /**
     * Метод получает все заявки на скидку, и создает сообщение на одобрение
     */
    public function __invoke(NewDiscountsOzonScheduleMessage $message): void
    {
        /** Получаем все токены профиля */

        $tokensByProfile = $this->OzonTokensByProfile
            ->onlyCardUpdate() // только обновляющие карточки
            ->findAll($message->getProfile());

        if(false === $tokensByProfile || false === $tokensByProfile->valid())
        {
            return;
        }

        foreach($tokensByProfile as $OzonTokenUid)
        {
            $discounts = $this->getOzonDiscountsRequest
                ->forTokenIdentifier($OzonTokenUid)
                ->findAll();

            if(false === $discounts || false === $discounts->valid())
            {
                return;
            }

            /** @var OzonDiscountDTO $OzonDiscountDTO */
            foreach($discounts as $OzonDiscountDTO)
            {

                /* TODO: ВРЕМЕННО ОТКЛОНЯЕМ ВСЕ ЗАЯВКИ !!! */

                $DiscountOzonMessage = new RejectDiscountOzonMessage(
                    profile: $message->getProfile(),
                    identifier: $OzonTokenUid,
                    id: $OzonDiscountDTO->getId(),
                );

                /**
                 * Отправляем сообщение на отмену скидки
                 */
                $this->messageDispatch->dispatch(
                    message: $DiscountOzonMessage,
                    transport: (string) $message->getProfile(),
                );


                continue;


                /** По умолчанию одобряем скидку до 10% */
                $DiscountOzonMessage = new ApproveDiscountOzonMessage(
                    $message->getProfile(),
                    $OzonDiscountDTO->getId(),
                    $OzonDiscountDTO->getRequested(),
                    $OzonDiscountDTO->getQuantity(),
                );

                /** Если скидка меньше минимальной - можем предоставить дополнительно 5% (суммарна до 10%) */
                if(false === $OzonDiscountDTO->isApproved())
                {
                    $price = $OzonDiscountDTO->getBasePrice(); // 8396
                    $requested = $OzonDiscountDTO->getRequested(); // 7585
                    $percentageChange = (($requested - $price) / $price) * 100;

                    /** Не одобряем скидку, если разница превысила 10% */
                    if($percentageChange < -10)
                    {
                        /** Отменяем заявку если превысила 10% */
                        $DiscountOzonMessage = new RejectDiscountOzonMessage(
                            profile: $message->getProfile(),
                            identifier: $OzonTokenUid,
                            id: $OzonDiscountDTO->getId(),
                        );
                    }
                }

                /**
                 * Отправляем сообщение на одобрение либо отмену скидки
                 */
                $this->messageDispatch->dispatch(
                    message: $DiscountOzonMessage,
                    transport: (string) $message->getProfile(),
                );
            }

        }
    }
}
