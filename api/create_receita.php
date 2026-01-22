<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireLogin();
$current_user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$input = getJsonInput();
$descricao = $input['descricao'] ?? '';
$valor = $input['valor'] ?? '';
$categoria = $input['categoria'] ?? 'Condomínio'; // Default category
$data = $input['data'] ?? date('Y-m-d');
$obs = $input['obs'] ?? '';

// Admin can specify WHO is paying (Sobrado ID)
// Normal user cannot add receipts in this model (User pays via bank, Admin records it)
// But if we want to keep flexibility, let's say Admin MUST add for Users.
if ($role === 'admin') {
    $target_user_id = $input['user_id'] ?? null;
    if (!$target_user_id) {
        jsonResponse(['error' => 'Selecione o Sobrado/Usuário.'], 400);
    }
} else {
    // Users normally don't create "Receitas" in Condo model, 
    // but if they do (e.g. proof of payment upload?), it goes to themselves.
    // For now, let's keep it restricted or allow same-user.
    // Prompt says "Admin deve adicionar esse valor". So maybe block Users?
    // "O admin deve adicionar esse valor para o usuario comum"
    // So only Admin creates Receitas.
    jsonResponse(['error' => 'Apenas o administrador pode registrar pagamentos.'], 403);
}

if (empty($descricao) || empty($valor)) {
    jsonResponse(['error' => 'Descrição e valor são obrigatórios.'], 400);
}

try {
    $stmt = $pdo->prepare("INSERT INTO receitas (user_id, descricao, valor, categoria, data, obs) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$target_user_id, $descricao, $valor, $categoria, $data, $obs]);
    jsonResponse(['message' => 'Receita adicionada com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao salvar receita.'], 500);
}
?>