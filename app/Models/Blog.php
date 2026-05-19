<?php

class Blog extends BaseModel
{
    protected string $table = 'blogs';
    protected array $fillable = [
        'user_id',
        'title',
        'content',
    ];

    public function createPost(int $userId, string $title, string $content): string
    {
        return $this->insert([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
        ]);
    }

    public function getAll(): array
    {
        return Database::query(
            'SELECT blogs.*, users.name AS author_name, users.role AS author_role
             FROM blogs
             INNER JOIN users ON users.id = blogs.user_id
             ORDER BY blogs.created_at DESC, blogs.id DESC'
        )->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $post = Database::query(
            'SELECT blogs.*, users.name AS author_name, users.role AS author_role
             FROM blogs
             INNER JOIN users ON users.id = blogs.user_id
             WHERE blogs.id = :id
             LIMIT 1',
            ['id' => $id]
        )->fetch();

        return $post ?: null;
    }

    public function deletePost(int $id): bool
    {
        return $this->delete($id);
    }

    public function isOwner(int $postId, int $userId): bool
    {
        return (bool) Database::query(
            'SELECT id FROM blogs WHERE id = :id AND user_id = :user_id LIMIT 1',
            ['id' => $postId, 'user_id' => $userId]
        )->fetch();
    }

    public function countAll(): int
    {
        return (int) Database::query('SELECT COUNT(*) FROM blogs')->fetchColumn();
    }
}
