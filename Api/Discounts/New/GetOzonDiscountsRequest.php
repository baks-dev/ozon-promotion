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

namespace BaksDev\Ozon\Promotion\Api\Discounts\New;

use BaksDev\Ozon\Api\Ozon;
use Generator;

final class GetOzonDiscountsRequest extends Ozon
{
    private string $status = 'NEW';

    public function unknown(): self
    {
        $this->status = 'UNKNOWN';
        return $this;
    }


    /**
     * Список заявок на скидку
     *
     * Метод для получения списка товаров, которые покупатели хотят купить со скидкой.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/promos_task_list
     *
     *
     * Статус заявки на скидку:
     *
     * NEW — новая,
     * SEEN — просмотренная,
     * APPROVED — одобренная,
     * PARTLY_APPROVED — одобренная частично,
     * DECLINED — отклонённая,
     * AUTO_DECLINED — отклонена автоматически,
     * DECLINED_BY_USER — отклонена покупателем,
     * COUPON — скидка по купону,
     * PURCHASED — купленная.
     *
     */
    public function findAll(): Generator|false
    {
        // APPROVED
        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/v1/actions/discounts-task/list',
                [
                    'json' => [
                        'status' => $this->status, // APPROVED
                        'page' => 1,
                        'limit' => 50
                    ]
                ]
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical($content['code'].': '.$content['message'], [self::class.':'.__LINE__]);
            return false;
        }

        if(empty($content['result']))
        {
            return false;
        }

        foreach($content['result'] as $item)
        {
            yield new OzonDiscountDTO($item);
        }
    }
}
