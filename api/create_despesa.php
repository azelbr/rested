<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin(); // Only Admin creates Despesas globally
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$input = getJsonInput();
$descricao = $input['descricao'] ?? '';
$valor = $input['valor'] ?? '';
$categoria = $input['categoria'] ?? 'Outros';
$data = $input['data'] ?? date('Y-m-d');
$obs = $input['obs'] ?? '';

if (empty($descricao) || empty($valor)) {
    jsonResponse(['error' => 'Descrição e valor são obrigatórios.'], 400);
}

try {
    $stmt = $pdo->prepare("INSERT INTO despesas (user_id, descricao, valor, categoria, data, obs) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $descricao, $valor, $categoria, $data, $obs]);
    jsonResponse(['message' => 'Despesa adicionada com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao salvar despesa.'], 500);
}
?>