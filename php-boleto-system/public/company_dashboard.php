<?php

declare(strict_types=1);

require __DIR__ . '/../app/auth.php';
$user = requireRole('company_admin');

$companyId = (int) $user['company_id'];

$summaryStmt = $pdo->prepare('SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = "pago" THEN 1 ELSE 0 END) AS pagos,
    SUM(CASE WHEN status = "pendente" THEN 1 ELSE 0 END) AS pendentes,
    SUM(CASE WHEN status = "atrasado" THEN 1 ELSE 0 END) AS atrasados,
    SUM(CASE WHEN status = "pago" THEN amount ELSE 0 END) AS valor_pago,
    SUM(CASE WHEN status IN ("pendente", "atrasado") THEN amount ELSE 0 END) AS valor_aberto
    FROM boletos WHERE company_id = :company_id');
$summaryStmt->execute(['company_id' => $companyId]);
$summary = $summaryStmt->fetch();

$listStmt = $pdo->prepare('SELECT * FROM boletos WHERE company_id = :company_id ORDER BY due_date ASC LIMIT 12');
$listStmt->execute(['company_id' => $companyId]);
$boletos = $listStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard da Empresa</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container d-flex justify-content-between">
    <span class="navbar-brand">Dashboard - <?= h($user['company_name']) ?></span>
    <div class="d-flex gap-2">
      <a href="/boleto_create.php" class="btn btn-light btn-sm">Novo boleto</a>
      <a href="/company_profile.php" class="btn btn-outline-light btn-sm">Cadastro da empresa</a>
      <a href="/logout.php" class="btn btn-outline-light btn-sm">Sair</a>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><small>Total de boletos</small><strong><?= (int) ($summary['total'] ?? 0) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><small>Pagos</small><strong><?= (int) ($summary['pagos'] ?? 0) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><small>Pendentes</small><strong><?= (int) ($summary['pendentes'] ?? 0) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><small>Atrasados</small><strong><?= (int) ($summary['atrasados'] ?? 0) ?></strong></div></div>
  </div>

  <div class="row g-3">
    <div class="col-md-6"><div class="card shadow-sm"><div class="card-body"><h2 class="h6">Valor pago</h2><p class="display-6">R$ <?= number_format((float) ($summary['valor_pago'] ?? 0), 2, ',', '.') ?></p></div></div></div>
    <div class="col-md-6"><div class="card shadow-sm"><div class="card-body"><h2 class="h6">Valor em aberto</h2><p class="display-6">R$ <?= number_format((float) ($summary['valor_aberto'] ?? 0), 2, ',', '.') ?></p></div></div></div>
  </div>

  <div class="card shadow-sm mt-4">
    <div class="card-body">
      <h2 class="h5">Últimos boletos</h2>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Tipo</th>
              <th>Vencimento</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Anexo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($boletos as $boleto): ?>
              <tr>
                <td><?= h($boleto['title']) ?></td>
                <td><?= h($boleto['boleto_type']) ?></td>
                <td><?= date('d/m/Y', strtotime($boleto['due_date'])) ?></td>
                <td>R$ <?= number_format((float) $boleto['amount'], 2, ',', '.') ?></td>
                <td><span class="badge text-bg-secondary"><?= h($boleto['status']) ?></span></td>
                <td>
                  <?php if ($boleto['attachment_path']): ?>
                    <a target="_blank" href="<?= h($boleto['attachment_path']) ?>">Visualizar</a>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
</body>
</html>
