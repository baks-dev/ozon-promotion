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

namespace BaksDev\Ozon\Promotion\Messenger\Schedules\ApproveDiscount;

use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final readonly class ApproveDiscountOzonMessage
{
    /**
     * Идентификатор заявки
     */
    private int $id;

    /**
     * Идентификатор профиля
     */
    private UserProfileUid $profile;

    /**
     * Согласованная цена.
     */
    private int|float $discount;


    /** Одобренное максимальное количество товаров. */
    private int $quantity;

    public function __construct(
        UserProfile|UserProfileUid|string $profile,
        int $id,
        int|float $discount,
        int $quantity
    )
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        $this->id = $id;

        $this->discount = $discount;
        $this->quantity = $quantity;
    }

    /**
     * Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Discount
     */
    public function getDiscount(): float|int
    {
        return $this->discount;
    }

    /**
     * Quantity
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

}