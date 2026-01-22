<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin();

try {
    // Get all users with their summary
    // Query optimized to get totals per user
    $sql = "SELECT u.id, u.nome, u.cpf, u.role, u.created_at,
            (SELECT COALESCE(SUM(valor), 0) FROM receitas WHERE user_id = u.id) as total_receitas,
            (SELECT COALESCE(SUM(valor), 0) FROM despesas WHERE user_id = u.id) as total_despesas
            FROM users u
            ORDER BY u.nome ASC";

    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();

    foreach ($users as &$user) {
        // Cast to float
        $user['total_receitas'] = (float) $user['total_receitas'];
        $user['total_despesas'] = (float) $user['total_despesas'];
        $user['saldo'] = $user['total_receitas'] - $user['total_despesas'];

        // Simple logic for status: Negative balance = Devedor
        // If we had due dates for expenses we could calculate 'parcelas atrasadas'
        // For now, simple balance check.
        $user['status_financeiro'] = $user['saldo'] >= 0 ? 'Em dia' : 'Pendente';
    }

    jsonResponse($users);

} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao listar usuários.'], 500);
}
?>