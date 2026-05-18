<section class="page-header">
    <h1>Edit Profile</h1>
    <p class="muted">Update your account details and profile picture.</p>
</section>

<?php if (!empty($errors)): ?>
    <div class="form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="form-card profile-form js-profile-form" method="post" action="<?= e(base_url('/profile/edit')) ?>" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">

    <div class="profile-picture-row">
        <?php if (!empty($user['profile_picture'])): ?>
            <img src="<?= e(base_url('/' . $user['profile_picture'])) ?>" alt="Profile picture">
        <?php else: ?>
            <div class="profile-placeholder"><?= e(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?></div>
        <?php endif; ?>

        <label>
            Profile Picture
            <input type="file" name="profile_picture" accept="image/jpeg,image/png">
        </label>
    </div>

    <label>
        Name
        <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" maxlength="100" required>
    </label>

    <label>
        Email
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" maxlength="150" required>
    </label>

    <label>
        Address
        <textarea name="address" rows="3"><?= e($old['address'] ?? '') ?></textarea>
    </label>

    <label>
        Phone
        <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" maxlength="20">
    </label>

    <fieldset>
        <legend>Change Password</legend>

        <label>
            Current Password
            <input type="password" name="current_password">
        </label>

        <label>
            New Password
            <input type="password" name="new_password" minlength="8">
        </label>

        <label>
            Confirm New Password
            <input type="password" name="confirm_password" minlength="8">
        </label>
    </fieldset>

    <button type="submit">Save Profile</button>
</form>
