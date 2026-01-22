<?php require 'src/head.php'; ?>

<main class="flex-1 flex flex-col justify-center p-6 bg-white sm:bg-slate-50">
    <div class="max-w-md w-full mx-auto sm:bg-white sm:p-8 sm:rounded-2xl sm:shadow-xl space-y-8">

        <div class="text-center space-y-2">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-500 text-white mb-4 shadow-lg shadow-brand-500/30">
                <ion-icon name="wallet-outline" class="text-3xl"></ion-icon>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Residencial Tedesco</h1>
            <p class="text-slate-500">Condomínio</p>
        </div>

        <form id="loginForm" class="space-y-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Usuário</label>
                    <input type="text" name="nome" placeholder="Ex: Sob 01" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Senha</label>
                    <input type="password" name="senha" placeholder="••••••••" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all placeholder:text-slate-400">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-brand-500/30 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <span>Entrar</span>
                <ion-icon name="arrow-forward"></ion-icon>
            </button>
        </form>

        <div class="text-center">
            <p class="text-slate-600 text-sm">Problemas com acesso? Contate o administrador.</p>
        </div>
    </div>
</main>

<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<ion-icon name="refresh" class="animate-spin text-xl"></ion-icon>';

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();

            if (res.ok) {
                window.location.href = json.redirect;
            } else {
                alert(json.error || 'Erro ao entrar');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (err) {
            console.error(err);
            alert('Erro de conexão');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
</script>
</body>

</html>