<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireLogin();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$year = $_GET['year'] ?? date('Y');

$whereUser = $role !== 'admin' ? "AND user_id = $user_id" : "";

$sql_rec = "SELECT MONTH(data) as mes, SUM(valor) as total FROM receitas WHERE YEAR(data) = ? $whereUser GROUP BY MONTH(data)";
$stmt = $pdo->prepare($sql_rec);
$stmt->execute([$year]);
$receitas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$sql_desp = "SELECT MONTH(data) as mes, SUM(valor) as total FROM despesas WHERE YEAR(data) = ? $whereUser GROUP BY MONTH(data)";
$stmt = $pdo->prepare($sql_desp);
$stmt->execute([$year]);
$despesas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

jsonResponse(['receitas' => $receitas, 'despesas' => $despesas]);
?>