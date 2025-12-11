<?php
require_once 'src/utils/auth.php';

// Redirect if already logged in
if (current_user()) {
    header("Location: /index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($email, $password)) {
        header("Location: /index.php");
        exit;
    } else {
        $error = "Identifiants invalides";
    }
}

include 'templates/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-900 text-white p-4">
    <div class="bg-gray-800 p-6 md:p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-700">
        <div class="text-center mb-8">
            <div class="inline-block p-3 rounded-full bg-gray-700 mb-4">
                <span class="text-4xl">🎺</span>
            </div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-600">
                Connexion
            </h1>
            <p class="text-gray-400 mt-2">Espace Membres & Administration</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-900/30 border border-red-500/50 text-red-200 p-4 rounded-lg mb-6 text-sm flex items-center gap-2">
                <span>⚠️</span> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5 text-gray-300">Email</label>
                <input
                    type="email"
                    name="email"
                    class="w-full p-3.5 rounded-lg bg-gray-700/50 border border-gray-600 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none transition-all text-white"
                    placeholder="votre@email.com"
                    required />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5 text-gray-300">Mot de passe</label>
                <input
                    type="password"
                    name="password"
                    class="w-full p-3.5 rounded-lg bg-gray-700/50 border border-gray-600 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none transition-all text-white"
                    placeholder="••••••••"
                    required />
            </div>
            <button
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-3.5 rounded-lg transition-all transform active:scale-[0.98] shadow-lg shadow-purple-900/20 cursor-pointer">
                Se connecter
            </button>
        </form>
    </div>
</div>

</body>

</html>