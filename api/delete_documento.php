<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin();

$input = getJsonInput();
$id = $input['id'] ?? null;

if (!$id) {
    jsonResponse(['error' => 'ID não fornecido'], 400);
}

// 1. Get file path
$stmt = $pdo->prepare("SELECT caminho FROM documentos WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    jsonResponse(['error' => 'Documento não encontrado'], 404);
}

// 2. Delete file from disk
$filePath = '../' . $doc['caminho'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// 3. Delete from DB
$stmt = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
$stmt->execute([$id]);

jsonResponse(['message' => 'Documento excluído com sucesso']);
?>
