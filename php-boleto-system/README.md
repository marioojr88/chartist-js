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

## Como executar localmente

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

---

## Hospedagem na Hostinger (passo a passo)

> Você já tem domínio e hospedagem, então siga este fluxo.

### 1) Preparar os arquivos
No seu computador, compacte a pasta `php-boleto-system` em `.zip`.

### 2) Enviar para a Hostinger
No hPanel:
1. Acesse **Arquivos > Gerenciador de arquivos**.
2. Entre em `public_html`.
3. Faça upload do `.zip`.
4. Extraia o arquivo.

Você terá algo como:

- `public_html/php-boleto-system/app`
- `public_html/php-boleto-system/public`
- `public_html/php-boleto-system/storage`

### 3) Definir como o domínio vai abrir o sistema
Você tem **2 opções**:

#### Opção A (recomendada): apontar o domínio para `php-boleto-system/public`
Se o plano permitir mudar o *Document Root*, configure o domínio para abrir a pasta `public`.

#### Opção B (mais simples): manter domínio na raiz do projeto
Se não conseguir mudar o *Document Root*, deixe o domínio apontando para `php-boleto-system/`.

Este projeto já possui arquivo `.htaccess` na raiz redirecionando para `public/` automaticamente.

### 4) Garantir segurança de pastas sensíveis
Este projeto inclui proteção por `.htaccess` em:
- `app/.htaccess`
- `storage/.htaccess`

Assim, essas pastas não devem ser acessadas via navegador.

### 5) Ativar suporte do PHP
No hPanel:
1. Vá em **Avançado > Configuração PHP**.
2. Use PHP 8.x.
3. Confirme extensões habilitadas:
   - `pdo`
   - `pdo_sqlite`
   - `sqlite3`

### 6) Criar o banco SQLite
Você precisa executar **uma vez** o inicializador:

```bash
php app/init_db.php
```

Se seu plano tiver terminal SSH, rode dentro de `php-boleto-system`.

Se não tiver terminal, peça ao suporte Hostinger para executar esse comando uma vez no caminho do seu projeto.

### 7) Permissões de escrita
Garanta permissão de escrita para:
- `storage/`
- `storage/uploads/`

Sem isso, o sistema não salvará banco/anexos.

### 8) Primeiro acesso
Abra seu domínio e entre com:
- `admin@master.com`
- `admin123`

Depois, troque a senha imediatamente.

### 9) SSL e produção
No hPanel:
1. Ative SSL do domínio.
2. Force HTTPS.

---

## Checklist rápido de publicação

- [ ] Arquivos enviados para Hostinger
- [ ] Domínio apontando para `public` (ou raiz com `.htaccess`)
- [ ] PHP 8.x com SQLite habilitado
- [ ] `init_db.php` executado
- [ ] Permissão de escrita em `storage/` e `storage/uploads/`
- [ ] Login do admin master funcionando
- [ ] Senha padrão alterada
