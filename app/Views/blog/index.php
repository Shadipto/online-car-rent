<section class="page-header">
    <h1>Blog</h1>
    <p class="muted">Read rental experiences from members and admins.</p>
</section>

<?php if (AuthMiddleware::isLoggedIn()): ?>
    <form class="form-card blog-form js-blog-form" method="post" action="<?= e(base_url('/api/blogs')) ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">

        <label>
            Title
            <input type="text" name="title" maxlength="200" required>
        </label>

        <label>
            Content
            <textarea name="content" rows="5" required></textarea>
        </label>

        <button type="submit">Post Experience</button>
        <p class="muted" data-blog-message></p>
    </form>
<?php else: ?>
    <p class="empty-state"><a href="<?= e(base_url('/login')) ?>">Login</a> to post your experience.</p>
<?php endif; ?>

<section class="section-block">
    <div class="section-heading">
        <h2>All Posts</h2>
    </div>

    <div class="blog-list" data-blog-list>
        <?php if ($posts === []): ?>
            <p class="empty-state" data-empty-blog>No blog posts yet.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php require app_path('Views/blog/_post.php'); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
