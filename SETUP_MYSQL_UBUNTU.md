# Configuração do Banco de Dados no Ubuntu Server

Para que o sistema funcione, você precisa criar o banco de dados e o usuário no MySQL do seu servidor (Ubuntu).

## 1. Acesse o MySQL no Terminal do Ubuntu
Rode o comando abaixo e digite a senha de **root** do MySQL (se tiver) ou apenas enter:
```bash
sudo mysql -u root -p
```

## 2. Crie o Banco e o Usuário
Copie e cole os comandos abaixo dentro do MySQL:

```sql
-- 1. Criar o Banco de Dados (com o nome que você definiu)
CREATE DATABASE IF NOT EXISTS ezyro_40904790_rested;

-- 2. Criar o Usuário 'ubuntu' com a senha '123456'
CREATE USER IF NOT EXISTS 'ubuntu'@'localhost' IDENTIFIED BY '123456';

-- 3. Dar permissão total para esse usuário no banco
GRANT ALL PRIVILEGES ON ezyro_40904790_rested.* TO 'ubuntu'@'localhost';

-- 4. Aplicar as mudanças
FLUSH PRIVILEGES;

-- 5. Sair
EXIT;
```

## 3. Importar as Tabelas
Agora que o banco existe, você precisa criar as tabelas. Se você tem o arquivo `database.sql` na pasta do site (`/var/www/html/residencial` por exemplo), rode:

```bash
mysql -u ubuntu -p ezyro_40904790_rested < database.sql
```
(Vai pedir a senha: `123456`)

---

## 4. Configurar o arquivo .env no Servidor
O sistema busca as senhas no arquivo `.env`.
Crie ou edite esse arquivo na pasta do site no servidor:

```bash
nano .env
```

Cole o seguinte conteúdo:

```ini
DB_HOST=localhost
DB_NAME=ezyro_40904790_rested
DB_USER=ubuntu
DB_PASS=123456
```

Salve (Ctrl+O, Enter) e saia (Ctrl+X).
