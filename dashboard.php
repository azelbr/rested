<?php
require_once 'src/db.php';
require_once 'src/auth.php';
require_once 'src/utils.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$nome = $_SESSION['nome'];

// Date Logic
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
$hoje = strftime('%d de %B de %Y', strtotime('today'));

// Status Logic (Reduced for Dashboard Header)
$month = date('n');
$year = date('Y');
$status_msg = "";
$status_color = "";

if ($role === 'normal') {
    $debt = calculateDebt($pdo, $user_id);

    if ($debt['count'] > 0) {
        $valFmt = number_format($debt['total'], 2, ',', '.');
        $status_msg = "Você tem {$debt['count']} mensalidades em atraso no total de R$ {$valFmt}.";
        $status_color = "bg-red-600";
    } else {
        $status_msg = "Parabéns! Seu condomínio está em dia.";
        $status_color = "bg-green-600";
    }
} else {
    // Admin Status
    $status_msg = "Painel Administrativo - Visão Geral";
    $status_color = "bg-brand-600";
}

require 'src/head.php';
?>

<body class="bg-slate-100 min-h-screen pb-32 font-sans overflow-y-auto">

    <!-- Header Customizado -->
    <header class="bg-blue-900 text-white p-6 rounded-b-3xl shadow-lg relative z-10">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="font-bold text-lg">Condomínio Residencial Tedesco</h1>
                <p class="text-sm text-blue-200">Seja bem-vindo, <?php echo htmlspecialchars($nome); ?></p>
            </div>
            <div class="bg-white/10 p-2 rounded-full">
                <ion-icon name="person" class="text-2xl"></ion-icon>
            </div>
        </div>

        <div class="text-xs text-blue-300 uppercase tracking-widest mb-4">
            Hoje é <?php echo $hoje; ?>
        </div>

        <!-- Status Card -->
        <div class="<?php echo $status_color; ?> p-3 rounded-xl shadow-md flex items-center gap-3 text-sm font-medium">
            <ion-icon name="alert-circle" class="text-xl"></ion-icon>
            <span><?php echo $status_msg; ?></span>
        </div>
    </header>

    <!-- Grid Menu -->
    <main class="p-4 -mt-2 relative z-20">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 max-w-6xl mx-auto">

            <!-- 1. Receitas -->
            <a href="receitas.php"
                class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center justify-center gap-2 aspect-[4/3] active:scale-95 transition-transform hover:shadow-md">
                <div class="bg-green-100 text-green-600 p-3 rounded-full">
                    <ion-icon name="cash-outline" class="text-2xl"></ion-icon>
                </div>
                <span class="font-bold text-slate-700 text-sm">Receitas</span>
            </a>

            <!-- 2. Despesas (Now pointing to Detailed) -->
            <a href="despesas_detalhadas.php"
                class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center justify-center gap-2 aspect-[4/3] active:scale-95 transition-transform hover:shadow-md">
                <div class="bg-red-100 text-red-600 p-3 rounded-full">
                    <ion-icon name="cart-outline" class="text-2xl"></ion-icon>
                </div>
                <span class="font-bold text-slate-700 text-sm">Despesas</span>
            </a>

            <!-- 4. Resumo -->
            <a href="resumo_financeiro.php"
                class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center justify-center gap-2 aspect-[4/3] active:scale-95 transition-transform hover:shadow-md">
                <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                    <ion-icon name="pie-chart-outline" class="text-2xl"></ion-icon>
                </div>
                <span class="font-bold text-slate-700 text-sm">Resumo</span>
            </a>

            <!-- 5. Documentos -->
            <a href="documentos.php"
                class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center justify-center gap-2 aspect-[4/3] active:scale-95 transition-transform hover:shadow-md">
                <div class="bg-purple-100 text-purple-600 p-3 rounded-full">
                    <ion-icon name="document-text-outline" class="text-2xl"></ion-icon>
                </div>
                <span class="font-bold text-slate-700 text-sm">Documentos</span>
            </a>

            <!-- 6. Mudar Senha -->
            <a href="mudar_senha.php"
                class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center justify-center gap-2 aspect-[4/3] active:scale-95 transition-transform hover:shadow-md">
                <div class="bg-slate-100 text-slate-600 p-3 rounded-full">
                    <ion-icon name="key-outline" class="text-2xl"></ion-icon>
                </div>
                <span class="font-bold text-slate-700 text-sm">Mudar Senha</span>
            </a>

        </div>
    </main>

    <?php require 'src/navbar.php'; ?>

</body>

</html>