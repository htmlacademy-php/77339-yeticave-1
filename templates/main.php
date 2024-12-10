<section class="promo">
    <h2 class="promo__title">Нужен стафф для катки?</h2>
    <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
    <ul class="promo__list">
        <?php
        $index = 0;
        $num = count($categories);
        while ($index < $num): ?>
        <li class="promo__item promo__item--boards">
            <a class="promo__link" href="pages/all-lots.html"><?= htmlspecialchars($categories[$index]);?></a>
        </li>
        <?php $index++; ?>
        <?php endwhile; ?>
    </ul>
</section>
<section class="lots">
    <div class="lots__header">
        <h2>Открытые лоты</h2>
    </div>
    <ul class="lots__list">
        <?php foreach ($lots as $lot): ?>
        <li class="lots__item lot">
            <div class="lot__image">
                <img src="<?=$lot['pic'];?>" width="350" height="260" alt="">
            </div>
            <div class="lot__info">
                <span class="lot__category"><?= htmlspecialchars($lot['category']);?></span>
                <h3 class="lot__title"><a class="text-link" href="pages/lot.html"><?=$lot['name'];?></a></h3>
                <div class="lot__state">
                    <div class="lot__rate">
                        <span class="lot__amount">Стартовая цена</span>
                        <span class="lot__cost"><?php echo format(htmlspecialchars($lot['price']));?></span>
                    </div>
                    <div class="lot__timer timer">
                        12:23
                    </div>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</section>