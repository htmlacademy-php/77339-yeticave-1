<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * Отправляет email победителю
 */
function sendWinnerEmail(array $params): void
{
    $transport = Transport::fromDsn(
        sprintf(
            'smtp://%s:%s@%s:%d',
            $params['config']['mailer']['user'],
            $params['config']['mailer']['password'],
            $params['config']['mailer']['smtp_server'],
            $params['config']['mailer']['smtp_port']
        )
    );

    $mailer = new Mailer($transport);

    $emailContent = getEmailTemplate([
        'winnerName' => $params['name'],
        'lotTitle' => $params['lotTitle'],
        'lotId' => $params['lotId'],
        'ratesLink' => $params['config']['site']['base_url'] . "/my-bets.php"
    ], $params['config']);

    $message = new Email();
    $message->from($params['config']['mailer']['user']);
    $message->to($params['email']);
    $message->subject('Ваша ставка победила');
    $message->html($emailContent);

    try {
        $mailer->send($message);
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
        echo 'Ошибка отправки письма: ' . $e->getMessage();
    }
}

/**
 * Генерация HTML-шаблона письма
 */
function getEmailTemplate(array $params, array $config): string
{
    $baseUrl = $config['site']['base_url'];
    $params['lotLink'] = $baseUrl . "/lot.php?id=" . $params['lotId'];

    return includeTemplate('email.php', $params);
}
