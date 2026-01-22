<nav
    class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 px-6 py-3 flex justify-between items-center z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">

    <!-- Inicio (Comum) -->
    <a href="dashboard.php"
        class="flex flex-col items-center justify-center w-full h-full space-y-1 text-brand-600 hover:text-brand-700 group">
        <ion-icon name="home" class="text-2xl transition-transform group-active:scale-90"></ion-icon>
        <span class="text-[10px] font-medium">Início</span>
    </a>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Receitas (Admin) -->
        <a href="receitas.php"
            class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-400 hover:text-brand-600 group">
            <ion-icon name="cash-outline" class="text-2xl transition-transform group-active:scale-90"></ion-icon>
            <span class="text-[10px] font-medium">Receitas</span>
        </a>

        <!-- Despesas (Admin) -->
        <a href="despesas_detalhadas.php"
            class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-400 hover:text-brand-600 group">
            <ion-icon name="cart-outline" class="text-2xl transition-transform group-active:scale-90"></ion-icon>
            <span class="text-[10px] font-medium">Despesas</span>
        </a>

        <!-- Usuários (Admin) -->
        <a href="usuarios.php"
            class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-400 hover:text-brand-600 group">
            <ion-icon name="people-outline" class="text-2xl transition-transform group-active:scale-90"></ion-icon>
            <span class="text-[10px] font-medium">Usuários</span>
        </a>

    <?php endif; ?>

    <!-- Sair (Comum) -->
    <a href="#" onclick="logout()"
        class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-400 hover:text-red-500 group">
        <ion-icon name="log-out-outline" class="text-2xl transition-transform group-active:scale-90"></ion-icon>
        <span class="text-[10px] font-medium">Sair</span>
    </a>

    <script>
        async function logout() {
            await fetch('api/logout.php');
            window.location.href = 'index.php';
        }
    </script>
</nav>