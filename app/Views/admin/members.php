<section class="page-header">
    <h1>Members</h1>
    <p class="muted">Delete member accounts when required.</p>
</section>

<?php if ($members === []): ?>
    <p class="empty-state">No members found.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="history-table admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr data-member-row="<?= e($member['id']) ?>">
                        <td><?= e($member['name']) ?></td>
                        <td><?= e($member['email']) ?></td>
                        <td><?= e($member['phone'] ?: 'Not set') ?></td>
                        <td><?= e($member['created_at']) ?></td>
                        <td>
                            <button class="button-danger js-delete-member" type="button" data-member-id="<?= e($member['id']) ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="muted" data-member-message></p>
<?php endif; ?>
