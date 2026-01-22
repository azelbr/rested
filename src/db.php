<?php
require_once __DIR__ . '/utils.php';

// 1. Prioridade: Arquivo de Configuração PHP (Ideal para Hospedagem Compartilhada)
// Crie um arquivo 'config.php' na mesma pasta 'src' com suas credenciais.
$configFile = __DIR__ . '/config.php';

if (file_exists($configFile)) {
    include $configFile;
    // O arquivo config.php deve definir: $host, $db, $user, $pass
} else {
    // 2. Fallback: Arquivo .env (Development / VPS)
    // Se não houver config.php, tenta ler do .env ou variáveis de ambiente
    if (function_exists('loadEnv')) {
        loadEnv(__DIR__ . '/../.env');
    }

    $host = getenv('DB_HOST') ?: 'localhost';
    $db = getenv('DB_NAME') ?: 'rested';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Mostra erro detalhado para ajudar no debug (pode remover depois por segurança)
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
?>