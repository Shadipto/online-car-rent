<?php

class ProfileController extends BaseController
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function edit(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $this->render('profile/edit', [
            'title' => 'Edit Profile',
            'user' => $user,
            'old' => $user,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $user = $this->currentUser();
        $data = $this->cleanInput($_POST);
        $errors = $this->validateInput($data, $user);
        $profilePicture = $user['profile_picture'] ?? null;

        if ($errors === [] && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$profilePicture, $uploadError] = $this->storeProfilePicture($_FILES['profile_picture'], $user['profile_picture'] ?? null);

            if ($uploadError !== null) {
                $errors['profile_picture'] = $uploadError;
            }
        }

        if ($errors !== []) {
            $this->render('profile/edit', [
                'title' => 'Edit Profile',
                'user' => $user,
                'old' => array_merge($user, $data),
                'errors' => $errors,
            ]);
            return;
        }

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'profile_picture' => $profilePicture,
        ];

        if ($data['new_password'] !== '') {
            $update['password_hash'] = password_hash($data['new_password'], PASSWORD_BCRYPT);
        }

        $this->users->updateProfile((int) $user['id'], $update);
        Session::set('name', $data['name']);
        Session::flash('success', 'Profile updated successfully.');
        $this->redirect('/profile/edit');
    }

    public function history(): void
    {
        $this->requireRole('member');
        $this->render('profile/history', [
            'title' => 'Rental History',
            'orders' => (new Order())->getByUser((int) Session::get('user_id')),
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    private function currentUser(): array
    {
        $user = $this->users->find((int) Session::get('user_id'));

        if ($user === null) {
            Session::destroy();
            $this->redirect('/login');
        }

        return $user;
    }

    private function cleanInput(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'address' => trim((string) ($input['address'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'current_password' => (string) ($input['current_password'] ?? ''),
            'new_password' => (string) ($input['new_password'] ?? ''),
            'confirm_password' => (string) ($input['confirm_password'] ?? ''),
        ];
    }

    private function validateInput(array $data, array $user): array
    {
        $errors = [];

        if ($data['name'] === '' || strlen($data['name']) > 100) {
            $errors['name'] = 'Name is required and must be under 100 characters.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 150) {
            $errors['email'] = 'Enter a valid email address under 150 characters.';
        } elseif ($this->users->emailExists($data['email'], (int) $user['id'])) {
            $errors['email'] = 'This email is already used by another account.';
        }

        if ($data['phone'] !== '' && strlen($data['phone']) > 20) {
            $errors['phone'] = 'Phone must be under 20 characters.';
        }

        if ($data['new_password'] !== '') {
            if (!password_verify($data['current_password'], $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            }

            if (strlen($data['new_password']) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters.';
            }

            if ($data['new_password'] !== $data['confirm_password']) {
                $errors['confirm_password'] = 'New passwords do not match.';
            }
        }

        return $errors;
    }

    private function storeProfilePicture(array $file, ?string $oldPath): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Profile picture upload failed.'];
        }

        if ((int) $file['size'] > (int) app_config('upload_max_size', 2 * 1024 * 1024)) {
            return [null, 'Profile picture must be 2MB or smaller.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        if (!isset($extensions[$mime])) {
            return [null, 'Profile picture must be a JPEG or PNG image.'];
        }

        $uploadDir = root_path('public/uploads/profiles');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = 'profile_' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [null, 'Could not save profile picture.'];
        }

        if ($oldPath) {
            $oldFile = root_path('public/' . ltrim($oldPath, '/'));
            $profilesDir = realpath($uploadDir);
            $oldReal = realpath($oldFile);

            if ($profilesDir && $oldReal && str_starts_with($oldReal, $profilesDir) && is_file($oldReal)) {
                unlink($oldReal);
            }
        }

        return ['uploads/profiles/' . $filename, null];
    }
}
