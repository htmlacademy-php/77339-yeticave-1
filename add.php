<?php
$classForm = (isset($errors)) ? "form--invalid" : "";
$class = isset($errors['lot-name']) ? "form__item--invalid" : "";
?>

<form class="form form--add-lot container <?= $classForm; ?>" action="../add.php" method="post" enctype="multipart/form-data">
    <h2>Добавление лота</h2>
    <div class="form__container-two">
        <div class="form__item <?= $class; ?>">
            <label for="lot-name">Наименование <sup>*</sup></label>
            <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота" value="<?= htmlspecialchars($lot['lot-name'] ?? ''); ?>">
            <?php
            if ($class): ?>
                <span class="form__error"><?= $errors['lot-name']; ?></span>
            <?php
            endif; ?>
        </div>
        <?php $class = isset($errors['category']) ? "form__item--invalid" : ""; ?>
        <div class="form__item <?= $class; ?>">
            <label for="category">Категория <sup>*</sup></label>
            <select id="category" name="category">
                <option>Выберите категорию</option>
                <?php
                foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['id']); ?>"
                        <?= isset($lot['category']) && $lot['category'] == $category['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category['name']); ?>
                    </option>
                <?php
                endforeach; ?>
            </select>
            <?php if ($class): ?>
            <span class="form__error"><?= $errors['category']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php $class = isset($errors['message']) ? "form__item--invalid" : ""; ?>
    <div class="form__item form__item--wide <?= $class; ?>">
        <label for="message">Описание <sup>*</sup></label>
        <textarea id="message" name="message" placeholder="Напишите описание лота"><?= htmlspecialchars($lot['message'] ?? ''); ?></textarea>
        <?php if ($class): ?>
        <span class="form__error"><?= $errors['message']; ?></span>
        <?php endif; ?>
    </div>
    <?php $class = isset($errors['file']) ? "form__item--invalid" : ""; ?>
    <div class="form__item form__item--file <?= $class; ?>">
        <label>Изображение <sup>*</sup></label>
        <div class="form__input-file">
            <input class="visually-hidden" type="file" id="lot-img" name="lot-img">
            <label for="lot-img">
                Добавить
            </label>
            <?php if ($class): ?>
                <span class="form__error"><?= $errors['file']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="form__container-three">
        <?php $class = isset($errors['lot-rate']) ? "form__item--invalid" : ""; ?>
        <div class="form__item form__item--small <?= $class; ?>">
            <label for="lot-rate">Начальная цена <sup>*</sup></label>
            <input id="lot-rate" type="text" name="lot-rate" placeholder="0" value="<?= htmlspecialchars($lot['lot-rate'] ?? ''); ?>">
            <?php if ($class): ?>
            <span class="form__error"><?= $errors['lot-rate']; ?></span>
            <?php endif; ?>
        </div>
        <?php $class = isset($errors['lot-step']) ? "form__item--invalid" : ""; ?>
        <div class="form__item form__item--small <?= $class; ?>">
            <label for="lot-step">Шаг ставки <sup>*</sup></label>
            <input id="lot-step" type="text" name="lot-step" placeholder="0" value="<?= htmlspecialchars($lot['lot-step'] ?? ''); ?>">
            <?php if ($class): ?>
            <span class="form__error"><?= $errors['lot-step']; ?></span>
            <?php endif; ?>
        </div>
        <?php $class = isset($errors['lot-date']) ? "form__item--invalid" : ""; ?>
        <div class="form__item <?= $class; ?>">
            <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
            <input class="form__input-date" id="lot-date" type="text" name="lot-date" placeholder="Введите дату в формате ГГГГ-ММ-ДД" value="<?= htmlspecialchars($lot['lot-date'] ?? ''); ?>">
            <?php if ($class): ?>
            <span class="form__error"><?= $errors['lot-date']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($classForm): ?>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <?php endif; ?>
    <button type="submit" class="button">Добавить лот</button>
</form>