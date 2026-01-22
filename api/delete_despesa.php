<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireLogin();
$input = getJsonInput();
$id = $input['id'] ?? 0;

if (!$id) {
    jsonResponse(['error' => 'ID da despesa inválido.'], 400);
}

$stmt = $pdo->prepare("SELECT user_id FROM despesas WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    jsonResponse(['error' => 'Despesa não encontrada.'], 404);
}

if ($_SESSION['role'] !== 'admin' && $item['user_id'] != $_SESSION['user_id']) {
    jsonResponse(['error' => 'Permissão negada.'], 403);
}

try {
    $pdo->prepare("DELETE FROM despesas WHERE id = ?")->execute([$id]);
    jsonResponse(['message' => 'Despesa excluída com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao excluir despesa.'], 500);
}
?>