<?php require 'src/head.php';
require 'src/auth.php';
requireLogin(); ?>

<body class="bg-gray-100 min-h-screen pb-24 font-sans overflow-y-auto">
    <header class="bg-slate-800 text-white p-4 shadow-md sticky top-0 z-10 flex justify-between items-center">
        <a href="dashboard.php"><ion-icon name="arrow-back" class="text-2xl"></ion-icon></a>
        <h1 class="font-bold text-lg">Alterar Senha</h1>
        <div class="w-6"></div>
    </header>

    <div class="p-6">
        <div class="bg-white rounded-2xl shadow-lg p-6 max-w-sm mx-auto mt-10">
            <h2 class="text-xl font-bold mb-4 text-slate-800">Nova Senha</h2>
            <form id="formPassword" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Digite sua nova senha</label>
                    <input type="password" name="nova_senha" required minlength="6" placeholder="Mínimo 6 caracteres"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                </div>
                <button type="submit"
                    class="w-full py-3 bg-brand-600 text-white font-bold rounded-xl shadow-lg hover:bg-brand-700 transition">Salvar
                    Nova Senha</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('formPassword').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            try {
                const res = await fetch('api/change_password.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
                const json = await res.json();
                alert(json.message || json.error);
                if (res.ok) window.location.href = 'dashboard.php';
            } catch (e) { alert('Erro'); }
        });
    </script>
    <?php require 'src/navbar.php'; ?>
</body>

</html>