<?php

declare(strict_types=1);

require __DIR__ . '/../app/auth.php';
$user = requireRole('master_admin');

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPassword = $_POST['admin_password'] ?? '';
    $photoPath = null;

    if ($name === '' || $cnpj === '' || $adminName === '' || $adminEmail === '' || $adminPassword === '') {
        $error = 'Preencha os campos obrigatórios da empresa e do admin.';
    } else {
        if (!empty($_FILES['photo']['name'])) {
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array(strtolower($extension), $allowed, true)) {
                $error = 'Formato de foto inválido. Use jpg, png ou webp.';
            } else {
                $fileName = uniqid('company_', true) . '.' . $extension;
                $target = __DIR__ . '/../storage/uploads/' . $fileName;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $photoPath = '/storage/uploads/' . $fileName;
                }
            }
        }

        if (!$error) {
            try {
                $pdo->beginTransaction();

                $companyStmt = $pdo->prepare('INSERT INTO companies (name, cnpj, phone, address, photo_path) VALUES (:name, :cnpj, :phone, :address, :photo_path)');
                $companyStmt->execute([
                    'name' => $name,
                    'cnpj' => $cnpj,
                    'phone' => $phone,
                    'address' => $address,
                    'photo_path' => $photoPath,
                ]);

                $companyId = (int) $pdo->lastInsertId();

                $userStmt = $pdo->prepare('INSERT INTO users (company_id, name, email, password_hash, role) VALUES (:company_id, :name, :email, :password_hash, :role)');
                $userStmt->execute([
                    'company_id' => $companyId,
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                    'role' => 'company_admin',
                ]);

                $pdo->commit();
                $message = 'Empresa e admin cadastrados com sucesso.';
            } catch (Throwable $exception) {
                $pdo->rollBack();
                $error = 'Erro ao cadastrar empresa: ' . $exception->getMessage();
            }
        }
    }
}

$companies = $pdo->query('SELECT companies.*, users.email AS admin_email, users.name AS admin_name FROM companies LEFT JOIN users ON users.company_id = companies.id AND users.role = "company_admin" ORDER BY companies.created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Master - Empresas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <span class="navbar-brand">Admin Master</span>
    <div class="d-flex align-items-center gap-3 text-white">
      <span><?= h($user['name']) ?></span>
      <a href="/logout.php" class="btn btn-outline-light btn-sm">Sair</a>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-3">Cadastrar nova empresa</h2>
          <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

          <form method="post" enctype="multipart/form-data" class="d-grid gap-2">
            <input class="form-control" name="name" placeholder="Nome da empresa" required>
            <input class="form-control" name="cnpj" placeholder="CNPJ" required>
            <input class="form-control" name="phone" placeholder="Telefone">
            <input class="form-control" name="address" placeholder="Endereço">
            <input class="form-control" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
            <hr>
            <input class="form-control" name="admin_name" placeholder="Nome do admin da empresa" required>
            <input class="form-control" type="email" name="admin_email" placeholder="E-mail do admin" required>
            <input class="form-control" type="password" name="admin_password" placeholder="Senha inicial" required>
            <button class="btn btn-primary mt-2">Salvar empresa</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-3">Empresas cadastradas</h2>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Empresa</th>
                  <th>CNPJ</th>
                  <th>Contato</th>
                  <th>Admin</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($companies as $company): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <?php if ($company['photo_path']): ?>
                          <img src="<?= h($company['photo_path']) ?>" class="thumb" alt="logo">
                        <?php endif; ?>
                        <div>
                          <strong><?= h($company['name']) ?></strong><br>
                          <small class="text-muted"><?= h($company['address']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td><?= h($company['cnpj']) ?></td>
                    <td><?= h($company['phone']) ?></td>
                    <td>
                      <?= h($company['admin_name']) ?><br>
                      <small class="text-muted"><?= h($company['admin_email']) ?></small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
</body>
</html>
