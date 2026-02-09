# Guia de Deploy na Hostinger (Laravel 11/12)

Este guia detalha o passo a passo para colocar o projeto Ghotme ERP no ar utilizando a hospedagem compartilhada da Hostinger.

## 1. Preparação dos Arquivos (Localmente)

Antes de subir os arquivos, você precisa preparar o pacote de produção.

1.  **Limpar caches de configuração:**
    ```bash
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```

2.  **Instalar dependências de produção:**
    ```bash
    composer install --optimize-autoloader --no-dev
    ```

3.  **Compilar os assets (Vite):**
    ```bash
    npm run build
    ```

4.  **Criar o arquivo ZIP:**
    Compacte todos os arquivos da raiz do projeto, **EXCETO**:
    - `node_modules/`
    - `.git/`
    - `.env` (vamos criar um novo lá)
    - `storage/*.key` (se houver)

## 2. Configuração no hPanel (Hostinger)

1.  **Banco de Dados:**
    - Vá em **Banco de Dados MySQL**.
    - Crie um novo banco de dados e um usuário.
    - Anote o *Nome do Banco*, *Usuário* e *Senha*.

2.  **Versão do PHP:**
    - Vá em **Configuração PHP**.
    - Certifique-se de que a versão selecionada é compatível com o Laravel 11/12 (Recomendado: **PHP 8.2** ou superior).
    - Em **Extensões PHP**, garanta que estão ativas: `fileinfo`, `pdo`, `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`.

## 3. Upload dos Arquivos

A estrutura de pastas recomendada para segurança no Hostinger é separar o núcleo do Laravel da pasta pública.

### Estrutura de Diretórios Sugerida:
```
/home/u123456789/
├── domains/
│   └── seudominio.com/
│       ├── public_html/   <-- Apenas o conteúdo da pasta 'public' do Laravel
│       └── ghotme_app/    <-- Todo o resto do código do Laravel
```

**Passo a Passo:**

1.  Acesse o **Gerenciador de Arquivos**.
2.  Na raiz do seu domínio (geralmente `domains/seudominio.com`), crie uma pasta chamada `ghotme_app`.
3.  Faça o upload do seu ZIP para dentro da pasta `ghotme_app` e extraia.
4.  Mova todo o conteúdo da pasta `ghotme_app/public` para a pasta `public_html`.
5.  Edite o arquivo `public_html/index.php`:

    Localize estas linhas e altere os caminhos:

    ```php
    // De:
    require __DIR__.'/../vendor/autoload.php';
    $app = require __DIR__.'/../bootstrap/app.php';

    // Para (ajuste o caminho conforme sua estrutura):
    require __DIR__.'/../ghotme_app/vendor/autoload.php';
    $app = require __DIR__.'/../ghotme_app/bootstrap/app.php';
    ```

## 4. Configuração do Ambiente (.env)

1.  Vá até a pasta `ghotme_app`.
2.  Renomeie o arquivo `.env.example` para `.env` (ou crie um novo).
3.  Edite o `.env` com as configurações da Hostinger:

    ```env
    APP_NAME=Ghotme
    APP_ENV=production
    APP_KEY=base64:... (Copie do seu .env local)
    APP_DEBUG=false
    APP_URL=https://seudominio.com

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=u123456789_nomedobanco
    DB_USERNAME=u123456789_usuario
    DB_PASSWORD=sua_senha_mysql
    ```

## 5. Migração do Banco de Dados

Se você tiver acesso SSH:
```bash
cd domains/seudominio.com/ghotme_app
php artisan migrate --force
```

**Se NÃO tiver acesso SSH:**
1.  Exporte seu banco de dados local (use o TablePlus, DBeaver ou mysqldump) para um arquivo `.sql`.
2.  No hPanel, acesse o **phpMyAdmin**.
3.  Selecione seu banco e clique em **Importar**.
4.  Envie o arquivo `.sql`.

## 6. Links Simbólicos (Storage)

As imagens salvas em `storage/app/public` não aparecerão se o link simbólico não existir.

**Com SSH:**
```bash
cd domains/seudominio.com/ghotme_app
php artisan storage:link
```
*Nota: Pode ser necessário mover o link gerado em `ghotme_app/public/storage` para `public_html/storage`.*

**Sem SSH (Script PHP):**
1.  Crie um arquivo chamado `link.php` na pasta `public_html`.
2.  Adicione o seguinte conteúdo:
    ```php
    <?php
    $target = '/home/u123456789/domains/seudominio.com/ghotme_app/storage/app/public';
    $shortcut = '/home/u123456789/domains/seudominio.com/public_html/storage';
    symlink($target, $shortcut);
    echo 'Link simbólico criado!';
    ?>
    ```
3.  Acesse `https://seudominio.com/link.php` no navegador.
4.  Apague o arquivo `link.php` após o uso.

## 7. Permissões de Pasta

Garanta que as seguintes pastas tenham permissão de escrita (775 ou 777 se necessário):
- `ghotme_app/storage`
- `ghotme_app/bootstrap/cache`

## Resolução de Problemas Comuns

-   **Tela Branca / Erro 500:** Verifique os logs em `ghotme_app/storage/logs/laravel.log`.
-   **Assets não carregam (CSS/JS):** Verifique se o `APP_URL` no `.env` está correto (https vs http). Como usamos `npm run build`, garanta que os arquivos em `public/build` foram enviados corretamente para `public_html/build`.
-   **Erro de Vite Manifest:** Se o erro informar que `manifest.json` não foi encontrado, verifique se a pasta `build` está em `public_html`.

---
Pronto! Seu sistema deve estar online. 🚀
