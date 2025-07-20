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

namespace BaksDev\Ozon\Products\Api\Discounts\New\Tests;

use BaksDev\Ozon\Orders\Type\ProfileType\TypeProfileFbsOzon;
use BaksDev\Ozon\Promotion\Api\Discounts\New\GetOzonDiscountsRequest;
use BaksDev\Ozon\Promotion\Api\Discounts\New\OzonDiscountDTO;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-promotion
 */
#[When(env: 'test')]
class GetOzonDiscountsRequestTest extends KernelTestCase
{
    private static OzonAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new OzonAuthorizationToken(
            new UserProfileUid('018d464d-c67a-7285-8192-7235b0510924'),
            $_SERVER['TEST_OZON_TOKEN'],
            TypeProfileFbsOzon::TYPE,
            $_SERVER['TEST_OZON_CLIENT'],
            $_SERVER['TEST_OZON_WAREHOUSE'],
            '10',
            0,
            false,
            false,
        );
    }


    public function testUseCase(): void
    {
        self::assertTrue(true);

        /** @var GetOzonDiscountsRequest $GetOzonDiscountsRequest */
        $GetOzonDiscountsRequest = self::getContainer()->get(GetOzonDiscountsRequest::class);
        $GetOzonDiscountsRequest->TokenHttpClient(self::$Authorization);

        $result = $GetOzonDiscountsRequest
            ->unknown()
            ->findAll();

        if(false !== $result)
        {
            foreach($result as $OzonDiscountDTO)
            {
                self::assertInstanceOf(OzonDiscountDTO::class, $OzonDiscountDTO);

                // процент разницы в цене
                $price = $OzonDiscountDTO->getBasePrice(); // 8396
                $requested = $OzonDiscountDTO->getRequested(); // 7585
                $percentageChange = (($requested - $price) / $price) * 100;

                self::assertIsInt($OzonDiscountDTO->getId());
                self::assertIsInt($OzonDiscountDTO->getRequested()); // Цена по заявке.
                self::assertIsInt($OzonDiscountDTO->getMinPrice()); // Минимальное значение цены
                self::assertIsInt($OzonDiscountDTO->getBasePrice()); // Минимальное значение цены

                /** Не одобряем скидку, если разница превысила 10% */
                //                if($percentageChange < -10)
                //                {
                //                    dump(sprintf('%s : Процент скидки БОЛЬШЕ 10%s  => %s', $OzonDiscountDTO->getId(), '%', round(abs($percentageChange))));
                //                    dd($OzonDiscountDTO);
                //                }
                //                else
                //                {
                //                    dump(sprintf('%s : Можно предоставить скидку %s процентов', $OzonDiscountDTO->getId(), round(abs($percentageChange))));
                //                    dd($OzonDiscountDTO);
                //                }
            }
        }
    }
}
