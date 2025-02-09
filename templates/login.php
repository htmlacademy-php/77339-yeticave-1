<?php $classname = isset($errors) ? "form--invalid" : ""; ?>

<form class="form container <?=$classname ?>" action="login.php" method="post"> <!-- form--invalid -->
    <h2>Вход</h2>
    <?php $classnameItem = isset($errors['email']) ? "form__item--invalid" : "";
    $value = isset($form['email']) ? $form['email'] : ""; ?>
    <div class="form__item <?=$classnameItem ?>"> <!-- form__item--invalid -->
        <label for="email">E-mail <sup>*</sup></label>
        <input id="email" type="text" name="email" placeholder="Введите e-mail" value="<?=$value; ?>">
        <?php if ($classnameItem) : ?>
        <span class="form__error"><?= $errors['email'] ; ?></span>
        <?php endif; ?>
    </div>
    <?php $classnameItem = isset($errors['password']) ? "form__item--invalid" : "";
    $value = isset($form['password']) ? $form['password'] : ""; ?>
    <div class="form__item form__item--last <?=$classnameItem ?>">
        <label for="password">Пароль <sup>*</sup></label>
        <input id="password" type="password" name="password" placeholder="Введите пароль" value="<?=$value; ?>">
        <?php if ($classnameItem) : ?>
        <span class="form__error"><?= $errors['password'] ; ?></span>
        <?php endif; ?>
    </div>
    <button type="submit" class="button">Войти</button>
</form>
