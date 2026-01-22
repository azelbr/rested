<?php
require_once '../src/db.php';
require_once '../src/auth.php';
require_once '../src/utils.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método inválido'], 405);
}

// Check file
if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => 'Erro no upload do arquivo.'], 400);
}

$titulo = $_POST['titulo'] ?? 'Documento';
$file = $_FILES['arquivo'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

if (!in_array($ext, $allowed)) {
    jsonResponse(['error' => 'Formato não permitido. Apenas PDF e Imagens.'], 400);
}

$filename = uniqid('doc_') . '.' . $ext;
$uploadDir = '../uploads/';

if (!is_dir($uploadDir))
    mkdir($uploadDir, 0755, true);

if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    // Save to DB
    try {
        $stmt = $pdo->prepare("INSERT INTO documentos (titulo, caminho) VALUES (?, ?)");
        $stmt->execute([$titulo, 'uploads/' . $filename]);
        header('Location: ../documentos.php'); // Redirect back simply
    } catch (PDOException $e) {
        echo "Erro ao salvar no banco.";
    }
} else {
    echo "Erro ao mover arquivo.";
}
?>