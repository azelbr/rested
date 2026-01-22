<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin();
$input = getJsonInput();
$id = $input['id'] ?? 0;

if (!$id) {
    jsonResponse(['error' => 'ID do usuário inválido.'], 400);
}

// Prevent self-deletion
if ($id == $_SESSION['user_id']) {
    jsonResponse(['error' => 'Você não pode se excluir.'], 400);
}

try {
    // Cascade delete handles financial records if set up in DB (ON DELETE CASCADE)
    // Create DB script included ON DELETE CASCADE, so we are good.
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    jsonResponse(['message' => 'Usuário excluído com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao excluir usuário.'], 500);
}
?>