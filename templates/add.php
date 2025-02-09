<?php
/** @var array $categories */
/** @var array $errors */
/** @var array $lotData */
$classnameForm = (isset($errors)) ? "form--invalid" : "";
?>

<form class="form form--add-lot container <?= $classnameForm; ?>" action="../add.php" method="post" enctype="multipart/form-data">
    <h2>Добавление лота</h2>
    <div class="form__container-two">
        <?php
        $classname = isset($errors['lot-designation']) ? "form__item--invalid" : ""; ?>
        <div class="form__item <?= $classname; ?>"> <!-- form__item--invalid -->
            <label for="lot-name">Наименование <sup>*</sup></label>
            <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота" value="<?= screening($lotData['lot-designation'] ?? ''); ?>">
            <?php
            if ($classname): ?>
                <span class="form__error"><?= $errors['lot-designation']; ?></span>
            <?php
            endif; ?>
        </div>
        <?php $classname = isset($errors['category']) ? "form__item--invalid" : ""; ?>
        <div class="form__item <?= $classname; ?>">
            <label for="category">Категория <sup>*</sup></label>
            <select id="category" name="category">
                <option>Выберите категорию</option>
                <?php
                foreach ($categories as $category): ?>
                    <option value="<?= screening($category['id']); ?>"
                        <?= isset($lotData['category']) && $lotData['category'] == $category['id'] ? 'selected' : ''; ?>>
                        <?= screening($category['designation']); ?>
                    </option>
                <?php
                endforeach; ?>
            </select>
            <?php if ($classname): ?>
            <span class="form__error"><?= $errors['category']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php $classname = isset($errors['message']) ? "form__item--invalid" : ""; ?>
    <div class="form__item form__item--wide <?= $classname; ?>">
        <label for="description">Описание <sup>*</sup></label>
        <textarea id="description" name="description" placeholder="Напишите описание лота"><?= screening($lotData['message'] ?? ''); ?></textarea>
        <?php if ($classname): ?>
        <span class="form__error"><?= $errors['description']; ?></span>
        <?php endif; ?>
    </div>
    <?php $classname = isset($errors['file']) ? "form__item--invalid" : ""; ?>
    <div class="form__item form__item--file <?= $classname; ?>">
        <label>Изображение <sup>*</sup></label>
        <div class="form__input-file">
            <input class="visually-hidden" type="file" id="lot-img" name="lot-img">
            <label for="lot-img">
                Добавить
            </label>
            <?php if ($classname): ?>
                <span class="form__error"><?= $errors['file']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="form__container-three">
        <?php $classname = isset($errors['lot-rate']) ? "form__item--invalid" : ""; ?>
        <div class="form__item form__item--small <?= $classname; ?>">
            <label for="lot-rate">Начальная цена <sup>*</sup></label>
            <input id="lot-rate" type="text" name="lot-rate" placeholder="0" value="<?= screening($lotData['lot-rate'] ?? ''); ?>">
            <?php if ($classname): ?>
            <span class="form__error"><?= $errors['lot-rate']; ?></span>
            <?php endif; ?>
        </div>
        <?php $classname = isset($errors['lot-step']) ? "form__item--invalid" : ""; ?>
        <div class="form__item form__item--small <?= $classname; ?>">
            <label for="lot-step">Шаг ставки <sup>*</sup></label>
            <input id="lot-step" type="text" name="lot-step" placeholder="0" value="<?= screening($lotData['lot-step'] ?? ''); ?>">
            <?php if ($classname): ?>
            <span class="form__error"><?= $errors['lot-step']; ?></span>
            <?php endif; ?>
        </div>
        <?php $classname = isset($errors['lot-date']) ? "form__item--invalid" : ""; ?>
        <div class="form__item <?= $classname; ?>">
            <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
            <input class="form__input-date" id="lot-date" type="text" name="lot-date" placeholder="Введите дату в формате ГГГГ-ММ-ДД" value="<?= screening($lotData['lot-date'] ?? ''); ?>">
            <?php if ($classname): ?>
            <span class="form__error"><?= $errors['lot-date']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($classnameForm): ?>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <?php endif; ?>
    <button type="submit" class="button">Добавить лот</button>
</form>
