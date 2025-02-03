<form class="form container" action="sign-up.php" method="post" autocomplete="off">
    <h2>Регистрация нового аккаунта</h2>
    <div class="form__item<?= isset($errors['email']) ? ' form__item--invalid' : '' ?>">
        <label for="email">E-mail <sup>*</sup></label>
        <input id="email" type="text" name="email" placeholder="Введите e-mail" value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
        <span class="form__error"><?= $errors['email'] ?? 'Введите e-mail' ?></span>
    </div>
    <div class="form__item<?= isset($errors['password']) ? ' form__item--invalid' : '' ?>">
        <label for="password">Пароль <sup>*</sup></label>
        <input id="password" type="password" name="password" placeholder="Введите пароль">
        <span class="form__error"><?= $errors['password'] ?? 'Введите пароль' ?></span>
    </div>
    <div class="form__item<?= isset($errors['name']) ? ' form__item--invalid' : '' ?>">
        <label for="name">Имя <sup>*</sup></label>
        <input id="name" type="text" name="name" placeholder="Введите имя" value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
        <span class="form__error"><?= $errors['name'] ?? 'Введите имя' ?></span>
    </div>
    <div class="form__item<?= isset($errors['message']) ? ' form__item--invalid' : '' ?>">
        <label for="message">Контактные данные <sup>*</sup></label>
        <textarea id="message" name="message" placeholder="Напишите как с вами связаться"><?= htmlspecialchars($formData['message'] ?? '') ?></textarea>
        <span class="form__error"><?= $errors['message'] ?? 'Напишите как с вами связаться' ?></span>
    </div>
    <?php if (!empty($errors)): ?>
        <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <?php endif; ?>
    <button type="submit" class="button">Зарегистрироваться</button>
    <a class="text-link" href="/pages/login.html">Уже есть аккаунт</a>
</form>
