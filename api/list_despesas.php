<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireLogin();
// Transparency: Everyone (Admin and Normal) sees ALL despesas
// No user_id filtering

$where = [];
$params = [];

if (isset($_GET['month']) && isset($_GET['year'])) {
    $where[] = "MONTH(d.data) = ?";
    $params[] = $_GET['month'];
    $where[] = "YEAR(d.data) = ?";
    $params[] = $_GET['year'];
}

$sql = "SELECT d.*, u.nome as usuario_nome FROM despesas d JOIN users u ON d.user_id = u.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY d.data DESC, d.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    jsonResponse($data);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao listar despesas.'], 500);
}
?>