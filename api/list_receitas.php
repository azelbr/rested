<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireLogin();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$where = [];
$params = [];

// Admin sees all receipts (Payments from all Sobrados)
// User sees only their own payments
if ($role !== 'admin') {
    $where[] = "r.user_id = ?";
    $params[] = $user_id;
}

if (isset($_GET['month']) && isset($_GET['year'])) {
    $where[] = "MONTH(r.data) = ?";
    $params[] = $_GET['month'];
    $where[] = "YEAR(r.data) = ?";
    $params[] = $_GET['year'];
}

// Join with users to get Sobrado Name
$sql = "SELECT r.*, u.nome as usuario_nome, u.id as user_id_real FROM receitas r JOIN users u ON r.user_id = u.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY u.nome ASC, r.data DESC"; // Order by Sobrado name then date

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    jsonResponse($data);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erro ao listar receitas.'], 500);
}
?>