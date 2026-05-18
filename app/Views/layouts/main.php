<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Session::csrfToken()) ?>">
    <meta name="app-base-url" content="<?= e(base_url('/')) ?>">
    <title><?= e($title ?? app_config('name', 'Online Car Rent')) ?></title>
    <link rel="stylesheet" href="<?= e(base_url('/assets/css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('/assets/css/auth.css')) ?>">
</head>
<body>
    <?php require app_path('Views/partials/navbar.php'); ?>

    <main class="site-main">
        <?php require app_path('Views/partials/flash.php'); ?>
        <?= $content ?>
    </main>

    <?php require app_path('Views/partials/footer.php'); ?>
    <script src="<?= e(base_url('/assets/js/validation.js')) ?>"></script>
    <script src="<?= e(base_url('/assets/js/auth.js')) ?>"></script>
    <script src="<?= e(base_url('/assets/js/orders.js')) ?>"></script>
    <script src="<?= e(base_url('/assets/js/blog.js')) ?>"></script>
</body>
</html>
