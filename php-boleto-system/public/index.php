<?php

declare(strict_types=1);

require __DIR__ . '/../app/auth.php';

if (currentUser()) {
    $user = currentUser();
    redirect($user['role'] === 'master_admin' ? '/master_dashboard.php' : '/company_dashboard.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT users.*, companies.name AS company_name FROM users LEFT JOIN companies ON companies.id = users.company_id WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        loginUser([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'company_id' => $user['company_id'],
            'company_name' => $user['company_name'] ?? null,
        ]);

        redirect($user['role'] === 'master_admin' ? '/master_dashboard.php' : '/company_dashboard.php');
    }

    $error = 'Credenciais inválidas.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Boletos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="bg-gradient">
  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card glass-card p-4 shadow-lg" style="max-width: 440px; width: 100%;">
      <h1 class="h3 fw-bold mb-3 text-center">Portal de Boletos</h1>
      <p class="text-muted text-center mb-4">Faça login para acessar o sistema.</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="post" class="d-grid gap-3">
        <div>
          <label class="form-label">E-mail</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div>
          <label class="form-label">Senha</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary btn-lg">Entrar</button>
      </form>

      <small class="text-muted mt-4 d-block">Master padrão: admin@master.com / admin123</small>
    </div>
  </div>
</body>
</html>
