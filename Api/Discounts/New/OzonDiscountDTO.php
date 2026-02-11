<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Ozon\Promotion\Api\Discounts\New;

final class OzonDiscountDTO
{
    /**
     * Идентификатор заявки.
     */
    private int $id;

    /**
     * Цена по заявке.
     */
    private int|float $requested;

    /**
     * Минимальное значение цены после
     */
    private int|float $min;

    /**
     * Базовая цена, по которой товар продаётся на Ozon, если не участвует в акции.
     */
    private int|float $base;

    /**
     * Запрошенное максимальное количество товаров.
     */
    private int $quantity;


    public function __construct(array $data)
    {
        $this->id = (int) $data['id'];
        $this->requested = $data['requested_price'];
        $this->min = $data['min_auto_price'];
        $this->base = $data['original_price'];
        $this->quantity = $data['requested_quantity_max'];


        /*

[
    'tasks' => [
        [
            'approved_discount' => 0,
            'approved_price' => 0,
            'approved_quantity_max' => 0,
            'auto_moderated_info' => [
                'max_percent' => 0,
                'max_price' => 0,
                'min_percent' => 0,
                'min_price' => 0
            ],
            'created_at' => '2019-08-24T14:15:22Z',
            'edited_till' => '2019-08-24T14:15:22Z',
            'edited_till_duration' => 0,
            'email' => 'string',
            'end_at' => '2019-08-24T14:15:22Z',
            'end_at_duration' => 0,
            'first_name' => 'string',
            'id' => 0,
            'is_auto_moderated' => true,
            'last_name' => 'string',
            'min_auto_price' => 0,
            'moderated_at' => '2019-08-24T14:15:22Z',
            'name' => 'string',
            'original_price' => 0,
            'patronymic' => 'string',
            'reduction_factor' => 0,
            'requested_discount' => 0,
            'requested_price' => 0,
            'requested_quantity_max' => 0,
            'sku' => 0,
            'status' => 'ALL'
        ]
    ]
];

    ]

 */


    }

    /**
     * Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Метод проверяет, что предоставленная пользователем скидка больше либо равна минимальной цене
     */
    public function isApproved(): bool
    {
        return $this->requested >= $this->min;
    }

    /**
     * Цена по заявке.
     */
    public function getRequested(): int|float
    {
        return $this->requested;
    }

    /**
     * Минимальное значение цены после
     */
    public function getMinPrice(): int|float
    {
        return $this->min;
    }

    /**
     * Количество в заявке
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Базовая стоимость товара
     */
    public function getBasePrice(): float|int
    {
        return $this->base;
    }

}
