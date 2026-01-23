<?php
require_once 'src/db.php';
require_once 'src/auth.php';

requireLogin();
$role = $_SESSION['role'];

// Fetch Docs
$docs = $pdo->query("SELECT * FROM documentos ORDER BY data_upload DESC")->fetchAll();

require 'src/head.php';
?>

<body class="bg-gray-100 min-h-screen pb-24 font-sans overflow-y-auto">
    <header class="bg-purple-800 text-white p-4 shadow-md sticky top-0 z-10 flex justify-between items-center">
        <a href="dashboard.php"><ion-icon name="arrow-back" class="text-2xl"></ion-icon></a>
        <h1 class="font-bold text-lg">Documentos</h1>
        <div class="w-6"></div>
    </header>

    <div class="p-4 space-y-6">

        <?php if ($role === 'admin'): ?>
            <!-- Upload Form -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-purple-100">
                <h3 class="font-bold text-purple-800 mb-3">Novo Documento</h3>
                <form action="api/upload_documento.php" method="POST" enctype="multipart/form-data" class="space-y-3">
                    <input type="text" name="titulo" placeholder="Nome do arquivo..."
                        class="w-full border-b border-slate-200 py-2 outline-none focus:border-purple-500" required>
                    <input type="file" name="arquivo"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
                        required>
                    <button type="submit"
                        class="w-full bg-purple-600 text-white font-bold py-2 rounded-lg hover:bg-purple-700">Enviar</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- List -->
        <div class="space-y-2">
            <?php if (empty($docs)): ?>
                <div class="text-center text-slate-400 py-5">Nenhum documento disponível.</div>
            <?php endif; ?>

            <?php foreach ($docs as $doc): ?>
                <a href="<?php echo htmlspecialchars($doc['caminho']); ?>" download
                    class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center gap-3 hover:bg-slate-50 active:scale-95 transition">
                    <div class="bg-purple-50 text-purple-600 p-2 rounded-lg">
                        <ion-icon name="document-text" class="text-2xl"></ion-icon>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-slate-700">
                            <?php echo htmlspecialchars($doc['titulo']); ?>
                        </p>
                        <p class="text-[10px] text-slate-400">
                            <?php echo date('d/m/Y', strtotime($doc['data_upload'])); ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <ion-icon name="download-outline" class="text-slate-400"></ion-icon>

                        <?php if ($role === 'admin'): ?>
                            <button onclick="event.preventDefault(); deleteDoc(<?php echo $doc['id']; ?>)"
                                class="text-red-400 hover:text-red-600 p-2 rounded-full hover:bg-red-50 transition"
                                title="Excluir">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Script de Exclusão -->
    <script>
        async function deleteDoc(id) {
            if (!confirm('Tem certeza que deseja excluir este documento?')) return;

            try {
                const res = await fetch('api/delete_documento.php', {
                    method: 'POST',
                    body: JSON.stringify({ id: id })
                });
                if (res.ok) window.location.reload();
                else alert('Erro ao excluir');
            } catch (e) {
                alert('Erro de conexão');
            }
        }
    </script>

    <?php require 'src/navbar.php'; ?>
</body>

</html>