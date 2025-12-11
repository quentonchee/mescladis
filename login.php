<?php
require_once 'src/utils/auth.php';

// Redirect if already logged in
if (current_user()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($email, $password)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Identifiants invalides";
    }
}

include 'templates/header.php';
?>

<div class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Decorative blobs -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 animate-blob"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary-500/20 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 animate-blob animation-delay-2000"></div>

    <div class="glass-panel p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-md relative z-10 animate-fade-in border-t border-white/10">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-brand-400 to-secondary-500 mb-6 shadow-lg shadow-brand-500/20">
                <span class="text-3xl">🎺</span>
            </div>
            <h1 class="text-3xl font-display font-bold text-white mb-2">
                Bienvenue
            </h1>
            <p class="text-slate-400 font-light">Connectez-vous à votre espace membre</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-200 p-4 rounded-xl mb-6 text-sm flex items-center gap-3 animate-slide-up">
                <span class="text-xl">⚠️</span>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-6">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300 ml-1">Email</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-brand-400 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input
                        type="email"
                        name="email"
                        class="block w-full pl-11 pr-4 py-3.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all duration-200 hover:bg-slate-800/80"
                        placeholder="votre@email.com"
                        required />
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300 ml-1">Mot de passe</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-brand-400 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        type="password"
                        name="password"
                        class="block w-full pl-11 pr-4 py-3.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all duration-200 hover:bg-slate-800/80"
                        placeholder="••••••••"
                        required />
                </div>
            </div>

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-brand-500 to-secondary-600 hover:from-brand-400 hover:to-secondary-500 text-white font-semibold py-4 rounded-xl transition-all transform active:scale-[0.98] shadow-lg shadow-brand-500/25 flex items-center justify-center gap-2 group">
                <span>Se connecter</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>
    </div>
</div>

</body>

</html>