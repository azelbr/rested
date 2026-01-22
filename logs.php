<?php
require_once 'src/auth.php';
require_once 'src/db.php';

requireAdmin();

// Buscar logs com nome do usuário
$stmt = $pdo->query("
    SELECT l.id, l.created_at, u.nome, u.role 
    FROM login_logs l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Acessos - Residencial Tedesco</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <!-- Navbar -->
    <nav class="bg-teal-600 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <span class="text-xl font-bold tracking-tight">Residencial Tedesco</span>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="hover:text-teal-200 transition-colors">Voltar ao Dashboard</a>
                    <button onclick="logout()" class="hover:text-red-200 transition-colors" title="Sair">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">📜 Histórico de Acessos</h2>
                <span class="text-sm text-gray-500">Últimos 100 acessos</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                            <th class="p-4 border-b">Data e Hora</th>
                            <th class="p-4 border-b">Usuário</th>
                            <th class="p-4 border-b">Perfil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4 text-sm text-gray-600">
                                        <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="p-4 font-medium text-gray-800">
                                        <?php echo htmlspecialchars($log['nome']); ?>
                                    </td>
                                    <td class="p-4">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php echo $log['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                            <?php echo ucfirst($log['role']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500">
                                    Nenhum acesso registrado ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            fetch('api/logout.php')
                .then(() => window.location.href = 'index.php');
        }
    </script>
</body>

</html>