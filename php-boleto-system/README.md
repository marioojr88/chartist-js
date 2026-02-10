# Sistema de Boletos (PHP + SQLite)

Sistema moderno para gestão de boletos com dois perfis:

- **Admin Master**: cadastra empresas e cria usuário administrador de cada empresa.
- **Admin Empresa**: cadastra boletos, faz upload do arquivo do boleto, acompanha dashboard e altera senha.

## Funcionalidades

- Login com controle de perfis.
- Cadastro de empresas com foto, CNPJ, telefone e endereço.
- Cadastro de boletos por tipo, data, valor e status.
- Upload de anexo (PDF/JPG/PNG) para cada boleto.
- Dashboard com métricas de pagamento.
- Página de cadastro da empresa com atualização de dados e troca de senha.

## Como executar

```bash
cd php-boleto-system
php app/init_db.php
php -S 0.0.0.0:8000 -t public
```

Acesse em `http://localhost:8000`.

### Credencial inicial

- E-mail: `admin@master.com`
- Senha: `admin123`

## Estrutura

- `app/`: configuração, autenticação e inicialização do banco.
- `public/`: páginas da aplicação.
- `storage/`: banco SQLite e arquivos enviados.
