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

namespace BaksDev\Ozon\Promotion\Api\Discounts;

use BaksDev\Ozon\Api\Ozon;
use InvalidArgumentException;

final class UpdateOzonRejectDiscountRequest extends Ozon
{
    private int $id;

    public function identifier(int|string $id): self
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Отменяет заявку на скидку
     *
     * @see https://docs.ozon.ru/api/seller/#operation/promos_task_decline
     *
     */
    public function reject(): bool
    {
        if($this->isExecuteEnvironment() === false)
        {
            return true;
        }

        if(empty($this->id))
        {
            throw new InvalidArgumentException('Invalid Argument Identifier');
        }

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/actions/discounts-task/decline',
                [
                    'json' => [
                        'tasks' => [
                            [
                                'id' => $this->id,
                                'seller_comment' => 'Мы вынуждены сообщить, что цена на эту продукцию очень сильно изменилась. К сожалению, в связи с этим мы не сможем предоставить дополнительную скидку. Благодарим вас за выбор нашего магазина и надеемся на ваше понимание!',
                            ]
                        ]
                    ]
                ]
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical($content['code'].': '.$content['message'], [self::class.':'.__LINE__]);
            return false;
        }

        if(empty($content['result']['success_count']))
        {
            return false;
        }

        return true;
    }
}
