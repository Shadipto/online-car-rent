<section class="auth-panel">
    <h1>Login</h1>
    <p class="muted">Use your registered account to continue.</p>

    <?php if (!empty($errors)): ?>
        <div class="form-errors">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form-card js-login-form" method="post" action="<?= e(base_url('/login')) ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">

        <label>
            Email
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" maxlength="150" required>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <label class="inline-check">
            <input type="checkbox" name="remember" value="1">
            Remember me
        </label>

        <button type="submit">Login</button>
    </form>
</section>
