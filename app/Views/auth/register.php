<section class="auth-panel">
    <h1>Create Account</h1>
    <p class="muted">Register as an admin or member for the car rent system.</p>

    <?php if (!empty($errors)): ?>
        <div class="form-errors">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form-card js-register-form" method="post" action="<?= e(base_url('/register')) ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">

        <label>
            Name
            <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" maxlength="100" required>
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" maxlength="150" required>
        </label>

        <label>
            Role
            <select name="role" required>
                <option value="member" <?= ($old['role'] ?? 'member') === 'member' ? 'selected' : '' ?>>Member</option>
                <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </label>

        <label>
            Address
            <textarea name="address" rows="3"><?= e($old['address'] ?? '') ?></textarea>
        </label>

        <label>
    Phone
    <div class="phone-group">
        <span class="phone-prefix">+880</span>

        <input
            type="tel"
            name="phone"
            value="<?= e($old['phone'] ?? '') ?>"
            maxlength="10"
            pattern="^1[3-9]\d{8}$"
            placeholder="1738037896"
            title="Enter a valid Bangladeshi phone number"
            required
        >
    </div>
</label>

        <label>
            Password
            <input type="password" name="password" minlength="8" required>
        </label>

        <label>
            Confirm Password
            <input type="password" name="confirm_password" minlength="8" required>
        </label>

        <button type="submit">Register</button>
    </form>
</section>
