<article class="car-card">
    <a href="<?= e(base_url('/cars/' . $car['id'])) ?>">
        <?php if (!empty($car['image_path'])): ?>
            <img src="<?= e(base_url('/' . $car['image_path'])) ?>" alt="<?= e($car['name']) ?>">
        <?php else: ?>
            <div class="image-placeholder">No image</div>
        <?php endif; ?>
    </a>
    <div class="car-card-body">
        <p class="eyebrow"><?= e($car['type']) ?></p>
        <h3><a href="<?= e(base_url('/cars/' . $car['id'])) ?>"><?= e($car['name']) ?></a></h3>
        <p><?= e($car['model']) ?></p>
        <strong>BDT <?= e(number_format((float) $car['price_per_day'], 2)) ?> / day</strong>
    </div>
</article>
