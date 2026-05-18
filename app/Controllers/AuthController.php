<?php

class AuthController extends BaseController
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function showRegister(): void
    {
        $this->render('auth/register', [
            'title' => 'Register',
            'old' => [],
            'errors' => [],
        ]);
    }

    public function register(): void
    {
        $this->verifyCsrf();

        $data = $this->cleanRegistrationInput($_POST);
        $errors = $this->validateRegistration($data);

        if ($errors !== []) {
            $this->render('auth/register', [
                'title' => 'Register',
                'old' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->users->createUser($data);
        Session::flash('success', 'Registration successful. Please log in.');
        $this->redirect('/login');
    }

    public function showLogin(): void
    {
        $this->render('auth/login', [
            'title' => 'Login',
            'old' => [],
            'errors' => [],
        ]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        $user = $errors === [] ? $this->users->findByEmail($email) : null;

        if ($errors === [] && ($user === null || !password_verify($password, $user['password_hash']))) {
            $errors['login'] = 'Invalid email or password.';
        }

        if ($errors !== []) {
            $this->render('auth/login', [
                'title' => 'Login',
                'old' => ['email' => $email],
                'errors' => $errors,
            ]);
            return;
        }

        $this->loginUser($user);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->users->setRememberToken((int) $user['id'], $token);
            $this->setRememberCookie($token);
        } else {
            $this->clearRememberCookie();
        }

        Session::flash('success', 'Welcome back, ' . $user['name'] . '.');
        $this->redirect('/');
    }

    public function logout(): void
    {
        $userId = Session::get('user_id');

        if (is_int($userId) || ctype_digit((string) $userId)) {
            $this->users->setRememberToken((int) $userId, null);
        }

        $this->clearRememberCookie();
        Session::destroy();
        $this->redirect('/login');
    }

    private function cleanRegistrationInput(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'password' => (string) ($input['password'] ?? ''),
            'confirm_password' => (string) ($input['confirm_password'] ?? ''),
            'role' => trim((string) ($input['role'] ?? 'member')),
            'address' => trim((string) ($input['address'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
        ];
    }

    private function validateRegistration(array $data): array
    {
        $errors = [];

        if ($data['name'] === '' || strlen($data['name']) > 100) {
            $errors['name'] = 'Name is required and must be under 100 characters.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 150) {
            $errors['email'] = 'Enter a valid email address under 150 characters.';
        } elseif ($this->users->emailExists($data['email'])) {
            $errors['email'] = 'This email is already registered.';
        }

        if (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!in_array($data['role'], ['admin', 'member'], true)) {
            $errors['role'] = 'Choose a valid role.';
        }

        if ($data['phone'] !== '' && strlen($data['phone']) > 20) {
            $errors['phone'] = 'Phone must be under 20 characters.';
        }

        return $errors;
    }

    private function loginUser(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('name', $user['name']);
        Session::set('role', $user['role']);
    }

    private function setRememberCookie(string $token): void
    {
        setcookie('remember_token', $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    private function clearRememberCookie(): void
    {
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }
}
