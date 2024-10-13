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

namespace BaksDev\Ozon\Products\Api\Discounts\New\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Ozon\Promotion\Api\Discounts\New\GetOzonDiscountsRequest;
use BaksDev\Ozon\Promotion\Api\Discounts\New\OzonDiscountDTO;
use BaksDev\Ozon\Promotion\Api\Discounts\UpdateOzonApproveDiscountRequest;
use BaksDev\Ozon\Type\Authorization\OzonAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group ozon-products
 */
#[When(env: 'test')]
class GetOzonDiscountsRequestTest extends KernelTestCase
{
    private static OzonAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new OzonAuthorizationToken(
            new UserProfileUid(),
            $_SERVER['TEST_OZON_TOKEN'],
            $_SERVER['TEST_OZON_CLIENT'],
            $_SERVER['TEST_OZON_WAREHOUSE'],
        );
    }


    public function testUseCase(): void
    {

        self::assertTrue(true);

        /** @var GetOzonDiscountsRequest $GetOzonDiscountsRequest */
        $GetOzonDiscountsRequest = self::getContainer()->get(GetOzonDiscountsRequest::class);
        $GetOzonDiscountsRequest->TokenHttpClient(self::$Authorization);

        /** @var UpdateOzonApproveDiscountRequest $UpdateOzonApproveDiscountRequest */
        $UpdateOzonApproveDiscountRequest = self::getContainer()->get(UpdateOzonApproveDiscountRequest::class);
        $UpdateOzonApproveDiscountRequest->TokenHttpClient(self::$Authorization);


        $result = $GetOzonDiscountsRequest->findAll();


        /** @var OzonDiscountDTO $OzonDiscountDTO */
        foreach($result as $OzonDiscountDTO)
        {
            $price = $OzonDiscountDTO->getMinPrice();

            if($OzonDiscountDTO->isApproved())
            {
                $requested = $OzonDiscountDTO->getRequested();

            }
            else
            {
                $requested = $OzonDiscountDTO->getBasePrice();
            }


            //$price = (int) (floor($requested / 1000) * 1000);


            //            if($OzonDiscountDTO->getMinPrice() > $price)
            //            {
            //                $price = (int) (floor($requested / 100) * 100);
            //            }

            //if($OzonDiscountDTO->getMinPrice() > $price)
            // {

            $requested = $OzonDiscountDTO->getMinPrice() + 100;
            $price = round($requested, -2);


            //$price = (int) (floor($requested / 100) * 100);
            // }

            if($OzonDiscountDTO->getMinPrice() > $price)
            {
                $requested = $OzonDiscountDTO->getMinPrice();
            }


            dump('запрашиваемая '.$requested);
            dump('окончательная цена '.$price);
            dump(' минимум '.$OzonDiscountDTO->getMinPrice());
            //dd('------------');

            //dump($OzonDiscountDTO);

            $approve = $UpdateOzonApproveDiscountRequest
                ->identifier($OzonDiscountDTO->getId())
                ->price($price)
                ->quantity($OzonDiscountDTO->getQuantity())
                ->approve();

            dd($approve);


            break;
        }

    }

}
