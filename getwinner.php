<?php

require_once 'data.php';

/** @var mysqli $db Ресурс подключения */
/** @var array $config Данные конфигурации*/

$lots = getLotsWithoutWinners($db);

if (!empty($lots)) {
    foreach ($lots as $lot) {
        handleEndedAuction($db, $lot['id']);
        $winnerId = getWinnerIdFromRates($db, $lot['id']);
        sendWinnerEmail([
            'email' => $lot['email'],
            'name' => $lot['name'],
            'lotTitle' => $lot['title'],
            'lotId' => $lot['id'],
            'config' => $config
        ]);
    }
} else {
    return;
}
