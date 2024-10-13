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

use App\Kernel;
use BaksDev\Ozon\Api\Ozon;
use DomainException;
use InvalidArgumentException;

final class UpdateOzonApproveDiscountRequest extends Ozon
{
    private int $id;

    private int|float $price;

    private int $quantity;

    public function identifier(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function price(int|float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function quantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Согласовать заявку на скидку
     *
     * Вы можете согласовывать заявки в статусах: NEW — новые, SEEN — просмотренные.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/promos_task_approve
     *
     */
    public function approve(): bool
    {


        if($this->isExecuteEnvironment() === false)
        {
            //return true;
        }

        if(empty($this->id))
        {
            throw new InvalidArgumentException('Invalid Argument Identifier');
        }

        if(empty($this->price))
        {
            throw new InvalidArgumentException('Invalid Argument Price');
        }

        if(empty($this->quantity))
        {
            throw new InvalidArgumentException('Invalid Argument Quantity');
        }

        //    "id": 0,
        //    "approved_price": 0,
        //    "seller_comment": "string",
        //    "approved_quantity_min": 0,
        //    "approved_quantity_max": 0

        // APPROVED
        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/actions/discounts-task/approve',
                [
                    'json' => [
                        'tasks' => [
                            [
                                'id' => $this->id,
                                'approved_price' => $this->price,
                                'seller_comment' => 'Благодарим вас за выбор нашего магазина! Мы очень рады, что вы решили приобрести нашу продукцию. Ваше доверие для нас имеет огромное значение, и мы уверены, что вы останетесь довольны покупкой. '.PHP_EOL.PHP_EOL.' Если у вас возникнут вопросы или потребуется помощь, не стесняйтесь обращаться к нашей команде.',
                                'approved_quantity_min' => $this->quantity,
                                'approved_quantity_max' => $this->quantity
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
