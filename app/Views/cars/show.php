<article class="car-detail">
    <div class="car-detail-media">
        <?php if (!empty($car['image_path'])): ?>
            <img src="<?= e(base_url('/' . $car['image_path'])) ?>" alt="<?= e($car['name']) ?>">
        <?php else: ?>
            <div class="image-placeholder">No image</div>
        <?php endif; ?>
    </div>

    <div class="car-detail-body">
        <p class="eyebrow"><?= e($car['type']) ?></p>
        <h1><?= e($car['name']) ?></h1>
        <p class="muted"><?= e($car['model']) ?></p>
        <p class="price">BDT <?= e(number_format((float) $car['price_per_day'], 2)) ?> / day</p>
        <p><?= e($car['description'] ?: 'No description has been added for this car yet.') ?></p>
        <p class="status <?= (int) $car['availability_status'] === 1 ? 'available' : 'unavailable' ?>">
            <?= (int) $car['availability_status'] === 1 ? 'Available' : 'Unavailable' ?>
        </p>
        <?php if (AuthMiddleware::isMember() && (int) $car['availability_status'] === 1): ?>
            <a class="button-link" href="<?= e(base_url('/orders/create/' . $car['id'])) ?>">Rent This Car</a>
        <?php elseif (!AuthMiddleware::isLoggedIn()): ?>
            <a class="button-link" href="<?= e(base_url('/login')) ?>">Login to Rent</a>
        <?php endif; ?>
        <a class="button-link" href="<?= e(base_url('/cars?type=' . urlencode($car['type']))) ?>">More <?= e($car['type']) ?></a>
    </div>
</article>
