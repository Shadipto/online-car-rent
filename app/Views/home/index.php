<section class="hero">
    <div>
        <p class="eyebrow">Online Car Rent</p>
        <h1>Find Your<br><em>Perfect Car</em></h1>
        <p>Browse available vehicles by category, view full rental details, and book your ride in minutes.</p>
        <a class="button-link" href="<?= e(base_url('/cars')) ?>">Browse Cars</a>
    </div>
</section>

<section class="section-block">
    <div class="section-heading">
        <h2>Categories</h2>
    </div>
    <div class="category-bar" data-category-bar>
        <?php foreach ($categories as $type): ?>
            <a href="<?= e(base_url('/cars?type=' . urlencode($type))) ?>" data-type="<?= e($type) ?>"><?= e($type) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block">
    <div class="section-heading">
        <h2>Featured Cars</h2>
        <a href="<?= e(base_url('/cars')) ?>">View all →</a>
    </div>
    <?php if ($featuredCars === []): ?>
        <p class="empty-state">No featured cars are available yet.</p>
    <?php else: ?>
        <div class="car-grid" data-car-grid>
            <?php foreach ($featuredCars as $car): ?>
                <?php require app_path('Views/partials/car-card.php'); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>