<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão com o Banco</h1>";

// 1. Check .env file
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "<p style='color:green'>✅ Arquivo .env encontrado.</p>";
} else {
    echo "<p style='color:red'>❌ Arquivo .env NÃO encontrado em: $envPath</p>";
    echo "<p>Crie o arquivo .env na raiz com o seguinte conteúdo:</p>";
    echo "<pre>DB_HOST=seu_host\nDB_NAME=seu_banco\nDB_USER=seu_usuario\nDB_PASS=sua_senha</pre>";
    exit;
}

// 2. Load .env manually for testing
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    putenv(trim($name) . '=' . trim($value));
}

// 3. Connect
$host = getenv('DB_HOST');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Banco:</strong> $db</li>";
echo "<li><strong>Usuário:</strong> $user</li>";
echo "<li><strong>Senha:</strong> " . ($pass ? "*** (definida)" : "Vazia") . "</li>";
echo "</ul>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    echo "<h2 style='color:green'>✅ CONEXÃO COM SUCESSO!</h2>";
    echo "<p>O PHP conseguiu entrar no banco de dados.</p>";
} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ FALHA NA CONEXÃO</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h3>Dicas:</h3>";
    echo "<ul>";
    echo "<li>Verifique se o usuário e senha estão corretos no arquivo .env</li>";
    echo "<li>Se for localhost, verifique se o MySQL está rodando.</li>";
    echo "<li>Se for VPS, verifique se criou o banco de dados e o usuário no painel/terminal.</li>";
    echo "</ul>";
}
?>
