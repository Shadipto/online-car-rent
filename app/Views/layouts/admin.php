<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Session::csrfToken()) ?>">
    <meta name="app-base-url" content="<?= e(base_url('/')) ?>">
    <title><?= e($title ?? 'Admin — ' . app_config('name', 'Online Car Rent')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(base_url('/assets/css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('/assets/css/auth.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('/assets/css/admin.css')) ?>">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a class="brand" href="<?= e(base_url('/admin/dashboard')) ?>"><?= e(app_config('name', 'Online Car Rent')) ?></a>
            <nav aria-label="Admin navigation">
                <a href="<?= e(base_url('/admin/dashboard')) ?>">Dashboard</a>
                <a href="<?= e(base_url('/admin/cars')) ?>">Cars</a>
                <a href="<?= e(base_url('/admin/members')) ?>">Members</a>
                <a href="<?= e(base_url('/admin/orders')) ?>">Orders</a>
                <a href="<?= e(base_url('/blog')) ?>">Blog</a>
                <a href="<?= e(base_url('/')) ?>">Site Home</a>
                <a href="<?= e(base_url('/logout')) ?>">Logout</a>
            </nav>
        </aside>
        <div class="admin-content">
            <?php require app_path('Views/partials/flash.php'); ?>
            <?= $content ?>
        </div>
    </div>
    <script src="<?= e(base_url('/assets/js/validation.js')) ?>"></script>
    <script src="<?= e(base_url('/assets/js/auth.js')) ?>"></script>
    <script src="<?= e(base_url('/assets/js/cars.js')) ?>"></script>
    <script src="<?= e(base_url('/assets/js/blog.js')) ?>"></script>
</body>
</html>