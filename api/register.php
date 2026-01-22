<?php
require_once '../src/db.php';
require_once '../src/utils.php';
require_once '../src/auth.php';

// Only Admin can create users now
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$input = getJsonInput();
$nome = $input['nome'] ?? '';
$cpf = $input['cpf'] ?? ''; // CPF still used for identification/record
$senha = $input['senha'] ?? '';

if (empty($nome) || empty($cpf) || empty($senha)) {
    jsonResponse(['error' => 'Preencha todos os campos.'], 400);
}

// Check by CPF (still unique ID)
$stmt = $pdo->prepare("SELECT id FROM users WHERE cpf = ?");
$stmt->execute([$cpf]);
if ($stmt->fetch()) {
    jsonResponse(['error' => 'CPF já cadastrado.'], 400);
}

// Check by Nome (Login ID) - Should be unique now
$stmt = $pdo->prepare("SELECT id FROM users WHERE nome = ?");
$stmt->execute([$nome]);
if ($stmt->fetch()) {
    jsonResponse(['error' => 'Nome de usuário já existe.'], 400);
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (nome, cpf, senha_hash, role) VALUES (?, ?, ?, 'normal')");
    $stmt->execute([$nome, $cpf, $senha_hash]);
    jsonResponse(['message' => 'Usuário criado com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro interno ao cadastrar.'], 500);
}
?>