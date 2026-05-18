<section class="page-header">
    <h1><?= $selectedType !== '' ? e($selectedType) : 'Cars' ?></h1>
    <p class="muted">Browse cars by category. Ordering is reserved for Task 3.</p>
</section>

<div class="category-bar" data-category-bar>
    <a href="<?= e(base_url('/cars')) ?>" class="<?= $selectedType === '' ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $type): ?>
        <a href="<?= e(base_url('/cars?type=' . urlencode($type))) ?>" class="<?= $selectedType === $type ? 'active' : '' ?>" data-type="<?= e($type) ?>"><?= e($type) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($cars === []): ?>
    <p class="empty-state">No cars found for this category.</p>
<?php else: ?>
    <div class="car-grid" data-car-grid>
        <?php foreach ($cars as $car): ?>
            <?php require app_path('Views/partials/car-card.php'); ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
