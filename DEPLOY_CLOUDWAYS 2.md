# Guia de Instalação na Cloudways (VPS)

Este guia ajuda a colocar o **Pep-Estoque** em produção usando um servidor Cloudways (Custom PHP).

## 1. Criar a Aplicação
1. No painel da Cloudways, clique em **Add Application**.
2. Selecione seu servidor.
3. Em "Application Type", escolha **Custom PHP**.
4. Dê um nome para a aplicação (ex: `pep-estoque`).

## 2. Acessar o Servidor via SSH
1. Em "Access Details" da aplicação, copie o **IP Publico**, **Username** e **Password** (Master User ou Application User).
2. Abra seu terminal e conecte-se:
   ```bash
   ssh master_user@ip_do_servidor
   ```
   (Cole a senha quando pedir).

## 3. Baixar o Código
Navegue até a pasta da aplicação. O nome da pasta geralmente é um código aleatório (você pode ver o nome exato em "Application Settings" > "Folder Name" no painel da Cloudways, ou dar um `ls` na pasta `applications`).

```bash
# Entre na pasta de aplicações
cd applications

# Liste para achar a sua
ls

# Entre na pasta da sua aplicação (exemplo)
cd nome_da_pasta_da_app

# Entre na pasta pública
cd public_html

# (Opcional) Remova o arquivo index.php padrão se existir
rm index.php
```

Agora, clone o repositório **dentro** da `public_html`:

```bash
# Clone o repositório (usando . para clonar na pasta atual)
git clone https://github.com/crisweiser17/estoque-suplementos .
```

## 4. Configurar Permissões do Banco de Dados
O SQLite precisa de permissão de escrita na pasta e no arquivo.

```bash
# Garanta que a pasta database e o arquivo sejam graváveis
chmod -R 775 database
chmod 664 database/database.sqlite 2>/dev/null || true
```
*Nota: Se o arquivo `database.sqlite` não existir, ele será criado automaticamente no primeiro acesso, desde que a pasta `database` tenha permissão.*

## 5. Ajustar o Webroot (Importante!)
Como o arquivo principal está em `public/index.php` e não na raiz, precisamos dizer para a Cloudways olhar para a pasta `public`.

1. Vá no Painel da Cloudways.
2. Selecione a Aplicação > **Application Settings**.
3. Vá na aba **App Settings**.
4. Em **Webroot**, altere de `public_html` para `public_html/public`.
5. Clique em **Save Changes**.

## 6. Testar
Acesse a URL da sua aplicação (fornecida no painel da Cloudways). O sistema deve carregar o Dashboard.

---

## Solução de Problemas Comuns

### Erro "Database connection failed" ou "ReadOnly"
Geralmente é permissão. Rode no terminal:
```bash
chown -R master:www-data database
chmod -R 775 database
```
(Substitua `master` pelo seu usuário principal se for diferente).

### Erro 404 em rotas
O servidor embutido do PHP faz roteamento automático, mas no Nginx/Apache da Cloudways precisamos garantir que as requisições passem pelo `index.php`.
Como estamos usando "Custom PHP", a Cloudways geralmente configura o Nginx para servir arquivos estáticos ou passar para o PHP. Se as rotas `/purchase`, `/sales` derem 404, precisamos de um arquivo `.htaccess` (se for Apache+Nginx) ou regra de Nginx.

A Cloudways usa Apache híbrido. Crie/Edite o arquivo `.htaccess` na pasta `public/`:

```bash
nano public/.htaccess
```

Cole o conteúdo:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```
Salve (Ctrl+O, Enter) e Saia (Ctrl+X).
