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
        $this->base = $data['base_price'];
        $this->quantity = $data['requested_quantity_max'];


        /*
        array:35 [
      "id" => 94438465107720019
      "created_at" => "2024-10-06T21:07:54.556591Z"
      "end_at" => "2024-10-06T21:07:54.556591Z"
      "edited_till" => "2024-10-07T18:11:18.688974Z"
      "status" => "DECLINED"
      "customer_name" => "Никита Ю."
      "sku" => 1714239185
      "user_comment" => ""
      "seller_comment" => ""
      "requested_price" => 8075
      "approved_price" => 0
      "original_price" => 8500
      "discount" => 4689
      "discount_percent" => 37
      "base_price" => 12764
      "min_auto_price" => 12041
      "prev_task_id" => 0
      "is_damaged" => false
      "moderated_at" => "2024-10-07T18:10:18.688974Z"
      "approved_discount" => 0
      "approved_discount_percent" => 0
      "is_purchased" => false
      "is_auto_moderated" => false
      "offer_id" => "PL01-19-235-55-105R"
      "email" => "baksdevelopment@gmail.com"
      "first_name" => ""
      "last_name" => ""
      "patronymic" => ""
      "approved_quantity_min" => 0
      "approved_quantity_max" => 0
      "requested_quantity_min" => 1
      "requested_quantity_max" => 1
      "requested_price_with_fee" => 0
      "approved_price_with_fee" => 0
      "approved_price_fee_percent" => 0
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
     * Quantity
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Base
     */
    public function getBasePrice(): float|int
    {
        return $this->base;
    }

}
