<?php

/**
 * @var string $winnerName Имя победителя
 * @var string $lotTitle Название лота
 * @var string $lotLink Ссылка на лот
 * @var string $ratesLink Ссылка на ставки
 */
?>

<h1>Поздравляем с победой!</h1>
<p>Здравствуйте, <?= screening($winnerName) ?></p>
<p>Ваша ставка для лота <a href="<?= screening($lotLink) ?>"><?= screening($lotTitle) ?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?= screening($ratesLink) ?>">мои ставки</a>, чтобы связаться с автором объявления.</p>
<small>Интернет-аукцион "YetiCave"</small>
