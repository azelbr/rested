<?php require 'src/head.php';
require 'src/auth.php';
requireLogin(); ?>

<body class="bg-slate-50 pb-24">
    <header class="bg-white p-4 shadow-sm sticky top-0 z-10 flex justify-between items-center">
        <h1 class="text-xl font-bold text-slate-800">Extrato Mensal</h1>
        <div class="flex gap-2">
            <select id="month" onchange="load()" class="bg-slate-100 p-2 rounded-lg text-sm">
                <?php for ($i = 1; $i <= 12; $i++)
                    echo "<option value='$i' " . ($i == date('n') ? 'selected' : '') . ">" . date('M', mktime(0, 0, 0, $i, 1)) . "</option>"; ?>
            </select>
            <select id="year" onchange="load()" class="bg-slate-100 p-2 rounded-lg text-sm">
                <?php echo "<option value='" . date('Y') . "'>" . date('Y') . "</option>"; ?>
            </select>
        </div>
    </header>
    <main class="p-4 space-y-4" id="content"></main>
    <?php require 'src/navbar.php'; ?>
    <script>
        async function load() {
            const m = document.getElementById('month').value;
            const y = document.getElementById('year').value;
            const content = document.getElementById('content');

            // Fetch both
            const [rec, desp] = await Promise.all([
                fetch(`api/list_receitas.php?month=${m}&year=${y}`).then(r => r.json()),
                fetch(`api/list_despesas.php?month=${m}&year=${y}`).then(r => r.json())
            ]);

            // Merge and sort
            const all = [
                ...rec.map(i => ({ ...i, type: 'rec' })),
                ...desp.map(i => ({ ...i, type: 'desp' }))
            ].sort((a, b) => new Date(b.data) - new Date(a.data));

            let html = '';
            let saldo = 0;

            all.forEach(i => {
                const val = parseFloat(i.valor);
                saldo += i.type === 'rec' ? val : -val;
                const color = i.type === 'rec' ? 'text-green-600' : 'text-red-600';
                const icon = i.type === 'rec' ? 'arrow-up-circle' : 'arrow-down-circle';

                html += `
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-50 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <ion-icon name="${icon}" class="${color} text-2xl"></ion-icon>
                <div>
                    <p class="font-bold text-slate-800">${i.descricao}</p>
                    <p class="text-xs text-slate-500">${new Date(i.data).toLocaleDateString('pt-BR')}</p>
                </div>
            </div>
            <span class="font-bold ${color}">R$ ${val.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
        </div>`;
            });

            const saldoClass = saldo >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            content.innerHTML = `
        <div class="p-4 rounded-xl ${saldoClass} flex justify-between items-center mb-4">
            <span class="font-bold">Saldo do Mês</span>
            <span class="font-bold text-lg">R$ ${saldo.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
        </div>
        <div class="space-y-3">${html}</div>
    `;
        }
        load();
    </script>
</body>

</html>