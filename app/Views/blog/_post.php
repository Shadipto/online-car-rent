<?php
$canDelete = AuthMiddleware::isAdmin()
    || (AuthMiddleware::isLoggedIn() && (int) $post['user_id'] === (int) Session::get('user_id'));
?>

<article class="blog-post" data-blog-post="<?= e($post['id']) ?>">
    <header>
        <div>
            <p class="eyebrow"><?= e(ucfirst($post['author_role'])) ?></p>
            <h3><?= e($post['title']) ?></h3>
            <p class="muted">By <?= e($post['author_name']) ?> on <?= e($post['created_at']) ?></p>
        </div>

        <?php if ($canDelete): ?>
            <button class="button-danger js-delete-blog" type="button" data-blog-id="<?= e($post['id']) ?>">Delete</button>
        <?php endif; ?>
    </header>

    <p><?= nl2br(e($post['content'])) ?></p>
</article>
