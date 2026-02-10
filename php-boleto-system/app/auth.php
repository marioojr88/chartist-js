<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): array
{
    $user = currentUser();

    if (!$user) {
        redirect('/index.php');
    }

    return $user;
}

function requireRole(string $role): array
{
    $user = requireLogin();

    if ($user['role'] !== $role) {
        if ($user['role'] === 'master_admin') {
            redirect('/master_dashboard.php');
        }

        redirect('/company_dashboard.php');
    }

    return $user;
}

function loginUser(array $user): void
{
    $_SESSION['user'] = $user;
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
