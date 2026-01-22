# ResTes - Guia de Deploy e Uso

## 1. Requisitos do Servidor
* PHP 7.4 ou superior.
* Servidor Web (Apache/Nginx).
* MySQL ou MariaDB.
* Suporte a HTTPS (Obrigatório para PWA).

## 2. Instalação do Banco de Dados
1. Acesse seu gerenciador de banco de dados (ex: PHPMyAdmin).
2. Crie um banco de dados chamado `residential_tedesco` (ou outro nome de sua preferência).
3. Importe o arquivo `database.sql` incluído na raiz do projeto.
   * Este script cria as tabelas `users`, `receitas`, `despesas`.
   * Cria também um usuário administrador padrão.

## 3. Configuração
1. Abra o arquivo `src/db.php`.
2. Edite as variáveis de conexão com suas credenciais:
   ```php
   $host = 'localhost';
   $db   = 'residential_tedesco';
   $user = 'seu_usuario';
   $pass = 'sua_senha';
   ```

## 4. Deploy (Upload)
1. Copie todos os arquivos e pastas para o diretório público do seu servidor (ex: `public_html` ou `www`).
2. **Importante**: Mantenha a estrutura de pastas (`api/`, `src/`, `assets/`).

## 5. Primeiro Acesso (Admin)
1. Acesse o sistema pelo navegador.
2. Utilize o usuário administrador padrão:
   * **CPF**: 000.000.000-00
   * **Senha**: admin123
3. Recomendado: Crie um novo usuário Admin e exclua o padrão, ou altere a senha no banco (hash Bcrypt).

## 6. PWA (Instalar no Celular)
1. O sistema deve ser acessado via **HTTPS** (salvo em localhost).
2. Abra o site no navegador do celular (Chrome ou Safari).
3. Chrome (Android):
   * Aguarde o banner "Adicionar à Tela Inicial" ou vá no menu e toque em "Instalar App".
4. Safari (iOS):
   * Toque no botão "Compartilhar".
   * Role para baixo e toque em "Adicionar à Tela de Início".
5. O ícone do ResTes aparecerá na sua lista de aplicativos.

## 7. Funcionalidades
* **Dashboard**: Visão geral de saldo e atalhos. Admin vê devedores.
* **Receitas/Despesas**: CRUD completo. Filtro por mês/ano.
* **Relatórios**: Extratos mensais e anuais gráficos.
* **Gestão de Usuários**: Apenas Admin. Pode excluir e visualizar saldos.
