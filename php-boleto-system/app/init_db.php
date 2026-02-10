<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

$pdo->exec('CREATE TABLE IF NOT EXISTS companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    cnpj TEXT NOT NULL UNIQUE,
    phone TEXT,
    address TEXT,
    photo_path TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ("master_admin", "company_admin")),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS boletos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    boleto_type TEXT NOT NULL,
    due_date TEXT NOT NULL,
    amount REAL NOT NULL,
    status TEXT NOT NULL CHECK(status IN ("pendente", "pago", "atrasado", "cancelado")),
    attachment_path TEXT,
    notes TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
)');

$masterEmail = 'admin@master.com';
$existingMaster = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$existingMaster->execute(['email' => $masterEmail]);

if (!$existingMaster->fetch()) {
    $stmt = $pdo->prepare('INSERT INTO users (company_id, name, email, password_hash, role) VALUES (NULL, :name, :email, :password_hash, :role)');
    $stmt->execute([
        'name' => 'Admin Master',
        'email' => $masterEmail,
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'master_admin',
    ]);
}

echo "Banco inicializado com sucesso.\n";
echo "Login master padrão: admin@master.com | Senha: admin123\n";
