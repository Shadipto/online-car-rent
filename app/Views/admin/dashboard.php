<section class="page-header">
    <h1>Admin Dashboard</h1>
    <p class="muted">Summary of the current car rental system data.</p>
</section>

<div class="stats-grid">
    <article class="stat-card">
        <span>Cars</span>
        <strong><?= e($counts['cars']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Members</span>
        <strong><?= e($counts['members']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Orders</span>
        <strong><?= e($counts['orders']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Blogs</span>
        <strong><?= e($counts['blogs']) ?></strong>
    </article>
</div>
