<?php

declare(strict_types=1);

require __DIR__ . '/../app/auth.php';
$user = requireRole('company_admin');

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $boletoType = trim($_POST['boleto_type'] ?? '');
    $dueDate = $_POST['due_date'] ?? '';
    $amount = (float) ($_POST['amount'] ?? 0);
    $status = $_POST['status'] ?? 'pendente';
    $notes = trim($_POST['notes'] ?? '');
    $attachmentPath = null;

    if ($title === '' || $boletoType === '' || $dueDate === '' || $amount <= 0) {
        $error = 'Preencha todos os campos obrigatórios.';
    }

    if (!$error && !empty($_FILES['attachment']['name'])) {
        $extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'png', 'jpg', 'jpeg'];
        if (!in_array($extension, $allowed, true)) {
            $error = 'Anexo inválido. Use PDF, JPG ou PNG.';
        } else {
            $fileName = uniqid('boleto_', true) . '.' . $extension;
            $target = __DIR__ . '/../storage/uploads/' . $fileName;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                $attachmentPath = '/storage/uploads/' . $fileName;
            }
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare('INSERT INTO boletos (company_id, title, boleto_type, due_date, amount, status, attachment_path, notes) VALUES (:company_id, :title, :boleto_type, :due_date, :amount, :status, :attachment_path, :notes)');
        $stmt->execute([
            'company_id' => $user['company_id'],
            'title' => $title,
            'boleto_type' => $boletoType,
            'due_date' => $dueDate,
            'amount' => $amount,
            'status' => $status,
            'attachment_path' => $attachmentPath,
            'notes' => $notes,
        ]);

        $message = 'Boleto cadastrado com sucesso.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Novo Boleto</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="app-bg">
<main class="container py-5" style="max-width: 760px;">
  <a href="/company_dashboard.php" class="btn btn-link mb-3">← Voltar ao dashboard</a>
  <div class="card shadow-sm">
    <div class="card-body">
      <h1 class="h4 mb-3">Cadastro de boleto</h1>
      <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nome do boleto</label>
          <input name="title" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tipo</label>
          <input name="boleto_type" class="form-control" placeholder="Energia, aluguel, fornecedor..." required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Data de vencimento</label>
          <input type="date" name="due_date" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Valor</label>
          <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="pendente">Pendente</option>
            <option value="pago">Pago</option>
            <option value="atrasado">Atrasado</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Anexo do boleto</label>
          <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <div class="col-12">
          <label class="form-label">Observações</label>
          <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-primary">Salvar boleto</button>
        </div>
      </form>
    </div>
  </div>
</main>
</body>
</html>
