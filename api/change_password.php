<?php
require_once '../src/db.php';
require_once '../src/utils.php';
require_once '../src/auth.php';

requireLogin();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$input = getJsonInput();
$senha_nova = $input['nova_senha'] ?? '';

if (empty($senha_nova) || strlen($senha_nova) < 6) {
    jsonResponse(['error' => 'A senha deve ter no mínimo 6 caracteres.'], 400);
}

$senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET senha_hash = ? WHERE id = ?");
    $stmt->execute([$senha_hash, $user_id]);
    jsonResponse(['message' => 'Senha alterada com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao alterar senha.'], 500);
}
?>