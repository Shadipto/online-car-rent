<?php

class User extends BaseModel
{
    protected string $table = 'users';
    protected array $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'profile_picture',
        'address',
        'phone',
        'remember_token',
    ];

    public function findByEmail(string $email): ?array
    {
        $user = Database::query(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        )->fetch();

        return $user ?: null;
    }

    public function findByRememberToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $user = Database::query(
            'SELECT * FROM users WHERE remember_token = :token LIMIT 1',
            ['token' => $token]
        )->fetch();

        return $user ?: null;
    }

    public function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($ignoreUserId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreUserId;
        }

        $sql .= ' LIMIT 1';

        return (bool) Database::query($sql, $params)->fetch();
    }

    public function createUser(array $data): string
    {
        return $this->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function updatePassword(int $id, string $password): bool
    {
        return $this->update($id, [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
    }

    public function setRememberToken(int $id, ?string $token): bool
    {
        return $this->update($id, ['remember_token' => $token]);
    }

    public function getAllMembers(): array
    {
        return Database::query(
            'SELECT id, name, email, phone, address, profile_picture, created_at
             FROM users
             WHERE role = :role
             ORDER BY created_at DESC, id DESC',
            ['role' => 'member']
        )->fetchAll();
    }

    public function deleteMember(int $id): bool
    {
        return Database::exec(
            'DELETE FROM users WHERE id = :id AND role = :role',
            ['id' => $id, 'role' => 'member']
        ) > 0;
    }

    public function countMembers(): int
    {
        return (int) Database::query(
            'SELECT COUNT(*) FROM users WHERE role = :role',
            ['role' => 'member']
        )->fetchColumn();
    }
}
