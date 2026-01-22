<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin(); // Only Admin updates receipts
$input = getJsonInput();
$id = $input['id'] ?? 0;
$descricao = $input['descricao'] ?? '';
$valor = $input['valor'] ?? '';
$categoria = $input['categoria'] ?? 'Condomínio';
$data = $input['data'] ?? date('Y-m-d');
$obs = $input['obs'] ?? '';

if (!$id || empty($descricao) || empty($valor)) {
    jsonResponse(['error' => 'Dados inválidos.'], 400);
}

try {
    $stmt = $pdo->prepare("UPDATE receitas SET descricao=?, valor=?, categoria=?, data=?, obs=? WHERE id=?");
    $stmt->execute([$descricao, $valor, $categoria, $data, $obs, $id]);
    jsonResponse(['message' => 'Receita atualizada!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao atualizar.'], 500);
}
?>