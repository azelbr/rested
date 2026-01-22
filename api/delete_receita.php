<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireLogin();
$input = getJsonInput();
$id = $input['id'] ?? 0;

if (!$id) {
    jsonResponse(['error' => 'ID da receita inválido.'], 400);
}

// Check if exists and belongs to user (or is admin)
$stmt = $pdo->prepare("SELECT user_id FROM receitas WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    jsonResponse(['error' => 'Receita não encontrada.'], 404);
}

if ($_SESSION['role'] !== 'admin' && $item['user_id'] != $_SESSION['user_id']) {
    jsonResponse(['error' => 'Permissão negada.'], 403);
}

try {
    $pdo->prepare("DELETE FROM receitas WHERE id = ?")->execute([$id]);
    jsonResponse(['message' => 'Receita excluída com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao excluir receita.'], 500);
}
?>