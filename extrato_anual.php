<?php require 'src/head.php';
require 'src/auth.php';
requireLogin(); ?>

<body class="bg-slate-50 pb-24">
    <header class="bg-white p-4 shadow-sm sticky top-0 z-10 flex justify-between items-center">
        <h1 class="text-xl font-bold text-slate-800">Extrato Anual</h1>
        <select id="year" onchange="load()" class="bg-slate-100 p-2 rounded-lg text-sm">
            <?php echo "<option value='" . date('Y') . "'>" . date('Y') . "</option>"; ?>
        </select>
    </header>
    <main class="p-4 space-y-4" id="content"></main>
    <?php require 'src/navbar.php'; ?>
    <script>
        async function load() {
            const y = document.getElementById('year').value;
            const content = document.getElementById('content');

            const data = await fetch(`api/extrato_anual.php?year=${y}`).then(r => r.json());

            let html = '';
            const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

            months.forEach((name, idx) => {
                const m = idx + 1;
                const r = parseFloat(data.receitas[m] || 0);
                const d = parseFloat(data.despesas[m] || 0);
                const saldo = r - d;
                const barR = r > 0 ? (r / (r + d + 1) * 100) : 0; // Simple calc for visual bar

                html += `
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-50 mb-3">
            <div class="flex justify-between items-center mb-2">
                <span class="font-bold text-slate-700">${name}</span>
                <span class="font-bold ${saldo >= 0 ? 'text-green-600' : 'text-red-600'}">R$ ${saldo.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
            </div>
            <div class="flex text-xs text-slate-500 justify-between mb-1">
                <span>Ent: ${r.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
                <span>Sai: ${d.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
            </div>
            <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden flex">
                <div class="h-full bg-green-500" style="width: ${r > 0 ? (r / (r + d) * 100) : 0}%"></div>
                <div class="h-full bg-red-500" style="width: ${d > 0 ? (d / (r + d) * 100) : 0}%"></div>
            </div>
        </div>`;
            });
            content.innerHTML = html;
        }
        load();
    </script>
</body>

</html>