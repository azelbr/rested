<?php
require_once '../src/db.php';
require_once '../src/utils.php';
require_once '../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$input = getJsonInput();
$nome = $input['nome'] ?? '';
$senha = $input['senha'] ?? '';

if (empty($nome) || empty($senha)) {
    jsonResponse(['error' => 'Preencha Usuário e senha.'], 400);
}

// Find user by nome (Login)
// Note: 'nome' must be unique enough. If duplicates exist, this might pick first.
// Admin is expected to create unique names like "Sobrado 01", "Sobrado 02".
$stmt = $pdo->prepare("SELECT id, nome, senha_hash, role FROM users WHERE nome = ?");
$stmt->execute([$nome]);
$user = $stmt->fetch();

if ($user && password_verify($senha, $user['senha_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nome'] = $user['nome'];
    $_SESSION['role'] = $user['role'];

    // Registrar Log de Login
    try {
        $logStmt = $pdo->prepare("INSERT INTO login_logs (user_id) VALUES (?)");
        $logStmt->execute([$user['id']]);
    } catch (Exception $e) {
        // Falha silenciosa no log não deve impedir o login
    }

    jsonResponse([
        'message' => 'Login realizado com sucesso!',
        'role' => $user['role'],
        'redirect' => 'dashboard.php'
    ]);
} else {
    jsonResponse(['error' => 'Usuário ou senha inválidos.'], 401);
}
?>