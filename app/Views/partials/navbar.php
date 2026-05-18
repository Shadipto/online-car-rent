<header class="site-header">
    <a class="brand" href="<?= e(base_url('/')) ?>"><?= e(app_config('name', 'Online Car Rent')) ?></a>
    <nav aria-label="Main navigation">
        <a href="<?= e(base_url('/cars')) ?>">Cars</a>
        <a href="<?= e(base_url('/blog')) ?>">Blog</a>

        <?php if (AuthMiddleware::isAdmin()): ?>
            <a href="<?= e(base_url('/admin/dashboard')) ?>">Admin Dashboard</a>
        <?php elseif (AuthMiddleware::isMember()): ?>
            <span class="nav-role">Member</span>
            <a href="<?= e(base_url('/profile/history')) ?>">Rental History</a>
        <?php endif; ?>

        <?php if (AuthMiddleware::isLoggedIn()): ?>
            <a href="<?= e(base_url('/profile/edit')) ?>">Profile</a>
            <a href="<?= e(base_url('/logout')) ?>">Logout</a>
        <?php else: ?>
            <a href="<?= e(base_url('/login')) ?>">Login</a>
            <a href="<?= e(base_url('/register')) ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
