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

namespace BaksDev\Ozon\Promotion\Commands;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Ozon\Promotion\Api\Discounts\New\GetOzonDiscountsRequest;
use BaksDev\Ozon\Promotion\Api\Discounts\New\OzonDiscountDTO;
use BaksDev\Ozon\Promotion\Messenger\ApproveDiscount\ApproveDiscountOzonMessage;
use BaksDev\Ozon\Repository\AllProfileToken\AllProfileOzonTokenInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Согласовать заявку на скидку
 */
#[AsCommand(
    name: 'baks:ozon-promotion:discount',
    description: 'Согласовать все заявки на скидку'
)]
class ApproveOzonDiscountCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileOzonTokenInterface $allProfileOzonToken,
        private readonly GetOzonDiscountsRequest $GetOzonDiscountsRequest,
        private readonly MessageDispatchInterface $messageDispatch
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        /**
         * Получаем активные токены авторизации профилей Ozon
         */
        $profiles = $this->allProfileOzonToken
            ->onlyActiveToken()
            ->findAll();

        $profiles = iterator_to_array($profiles);

        $helper = $this->getHelper('question');

        /**
         * Интерактивная форма списка профилей
         */

        $questions[] = 'Все';

        foreach($profiles as $quest)
        {
            $questions[] = $quest->getAttr();
        }

        $questions['+'] = 'Выполнить все асинхронно';
        $questions['-'] = 'Выйти';

        $question = new ChoiceQuestion(
            'Профиль пользователя (Ctrl+C чтобы выйти)',
            $questions,
            '+'
        );

        $profileName = $helper->ask($input, $output, $question);

        /**
         *  Выходим без выполненного запроса
         */

        if($profileName === '-' || $profileName === 'Выйти')
        {
            return Command::SUCCESS;
        }


        /**
         * Выполняем все с возможностью асинхронно в очереди
         */

        if($profileName === '+' || $profileName === '0' || $profileName === 'Все')
        {
            /** @var UserProfileUid $profile */
            foreach($profiles as $profile)
            {
                $this->update($profile, $profileName === '+');
            }

            $this->io->success('Все заявки на скидку успешно согласованы');
            return Command::SUCCESS;
        }

        /**
         * Выполняем определенный профиль
         */

        $UserProfileUid = null;

        foreach($profiles as $profile)
        {
            if($profile->getAttr() === $questions[$profileName])
            {
                /* Присваиваем профиль пользователя */
                $UserProfileUid = $profile;
                break;
            }
        }

        if($UserProfileUid)
        {
            $this->update($UserProfileUid);

            $this->io->success('Все заявки на скидку успешно согласованы');
            return Command::SUCCESS;
        }


        $this->io->success('Профиль пользователя не найден');
        return Command::SUCCESS;
    }

    public function update(UserProfileUid $profile, bool $async = false): void
    {
        /** Получаем все заявки на скидку */
        $discounts = $this->GetOzonDiscountsRequest
            ->profile($profile)
            ->findAll();

        if($discounts)
        {
            $this->io->note(sprintf('Обновляем профиль %s', $profile->getAttr()));

            /** @var OzonDiscountDTO $OzonDiscountDTO */
            foreach($discounts as $OzonDiscountDTO)
            {
                $ApproveDiscountOzonMessage = new ApproveDiscountOzonMessage(
                    $profile,
                    $OzonDiscountDTO->getId(),
                    $OzonDiscountDTO->getMinPrice(),
                    $OzonDiscountDTO->getQuantity()
                );

                /** В консоли выполняем сообщение синхронно */
                $this->messageDispatch->dispatch(
                    message: $ApproveDiscountOzonMessage,
                    transport: $async ? (string) $profile : null
                );

                $this->io->writeln(sprintf('<fg=green>Согласовали заявку %s</>', $OzonDiscountDTO->getId()));

            }
        }
        else
        {
            $this->io->writeln(sprintf('Заявок на скидку профиля %s не найдено', $profile->getAttr()));
        }

    }
}
