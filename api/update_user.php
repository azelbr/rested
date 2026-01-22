<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin();

$input = getJsonInput();
$id = $input['id'] ?? null;
$nome = $input['nome'] ?? '';
$cpf = $input['cpf'] ?? '';
$senha = $input['senha'] ?? ''; // Optional
// debt_start_date might be empty, check.
$debt_start_date = !empty($input['debt_start_date']) ? $input['debt_start_date'] : '2024-01-01';

if (!$id || empty($nome) || empty($cpf)) {
    jsonResponse(['error' => 'Dados incompletos'], 400);
}

try {
    if (!empty($senha)) {
        // Update with password
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET nome = ?, cpf = ?, senha_hash = ?, debt_start_date = ? WHERE id = ?");
        $stmt->execute([$nome, $cpf, $hash, $debt_start_date, $id]);
    } else {
        // Update without password
        $stmt = $pdo->prepare("UPDATE users SET nome = ?, cpf = ?, debt_start_date = ? WHERE id = ?");
        $stmt->execute([$nome, $cpf, $debt_start_date, $id]);
    }

    // Also update role? Not requested yet. Assume keeping role.

    jsonResponse(['message' => 'Usuário atualizado com sucesso']);

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        jsonResponse(['error' => 'Nome ou CPF já existe.'], 400);
    }
    jsonResponse(['error' => 'Erro ao atualizar usuário.'], 500);
}
?>