<?php foreach (Session::getFlash() as $type => $messages): ?>
    <?php foreach ($messages as $message): ?>
        <div class="flash flash-<?= e($type) ?>" role="status">
            <?= e($message) ?>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
