<?php

declare(strict_types=1);

require __DIR__ . '/../app/auth.php';
$user = requireRole('company_admin');

$companyStmt = $pdo->prepare('SELECT * FROM companies WHERE id = :id LIMIT 1');
$companyStmt->execute(['id' => $user['company_id']]);
$company = $companyStmt->fetch();

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_company'])) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $stmt = $pdo->prepare('UPDATE companies SET name = :name, phone = :phone, address = :address WHERE id = :id');
        $stmt->execute([
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'id' => $user['company_id'],
        ]);
        $_SESSION['user']['company_name'] = $name;
        $success = 'Cadastro da empresa atualizado.';
    }

    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';

        $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $userStmt->execute(['id' => $user['id']]);
        $dbUser = $userStmt->fetch();

        if (!$dbUser || !password_verify($current, $dbUser['password_hash'])) {
            $error = 'Senha atual inválida.';
        } elseif (strlen($new) < 6) {
            $error = 'A nova senha precisa ter ao menos 6 caracteres.';
        } else {
            $update = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
            $update->execute([
                'password_hash' => password_hash($new, PASSWORD_DEFAULT),
                'id' => $user['id'],
            ]);
            $success = 'Senha atualizada com sucesso.';
        }
    }

    $companyStmt->execute(['id' => $user['company_id']]);
    $company = $companyStmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro da Empresa</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="app-bg">
<main class="container py-5" style="max-width: 840px;">
  <a href="/company_dashboard.php" class="btn btn-link mb-3">← Voltar ao dashboard</a>
  <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-md-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-3">Cadastro da empresa</h2>
          <form method="post" class="d-grid gap-2">
            <input type="hidden" name="update_company" value="1">
            <label class="form-label mb-0">Nome da empresa</label>
            <input class="form-control" name="name" value="<?= h($company['name']) ?>" required>
            <label class="form-label mb-0">CNPJ</label>
            <input class="form-control" value="<?= h($company['cnpj']) ?>" disabled>
            <label class="form-label mb-0">Telefone</label>
            <input class="form-control" name="phone" value="<?= h($company['phone']) ?>">
            <label class="form-label mb-0">Endereço</label>
            <input class="form-control" name="address" value="<?= h($company['address']) ?>">
            <button class="btn btn-primary mt-2">Atualizar cadastro</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-3">Alterar senha</h2>
          <form method="post" class="d-grid gap-2">
            <input type="hidden" name="change_password" value="1">
            <label class="form-label mb-0">Senha atual</label>
            <input type="password" class="form-control" name="current_password" required>
            <label class="form-label mb-0">Nova senha</label>
            <input type="password" class="form-control" name="new_password" minlength="6" required>
            <button class="btn btn-dark mt-2">Atualizar senha</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>
</body>
</html>
