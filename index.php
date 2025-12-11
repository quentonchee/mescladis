<?php
require_once 'src/utils/auth.php';
require_once 'src/models/Event.php';

require_login();
$user = current_user();
$eventModel = new Event();

// Handle Attendance Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventId'], $_POST['status'])) {
    $result = $eventModel->updateAttendance($user['id'], $_POST['eventId'], $_POST['status']);
    // To prevent resubmission on refresh
    header("Location: index.php");
    exit;
}

$events = $eventModel->getAllFutureWithUserStatus($user['id']);

include 'templates/header.php';
?>

<div class="relative min-h-screen pb-12">
    <!-- Decorative background elements -->
    <div class="fixed top-0 left-0 w-full h-96 bg-gradient-to-b from-brand-900/20 to-transparent pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 md:pt-12 relative z-10">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6 animate-slide-up">
            <div>
                <h1 class="text-4xl md:text-5xl font-display font-bold text-white mb-2">
                    Bonjour, <span class="bg-clip-text text-transparent bg-gradient-to-r from-brand-400 to-secondary-400"><?= htmlspecialchars($user['name'] ?: 'Musicien') ?></span>
                </h1>
                <p class="text-slate-400 text-lg font-light">Voici le programme des répétitions et concerts.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <?php
                $isAdmin = ($user['role'] === 'ADMIN');
                if ($isAdmin): ?>
                    <a href="admin/events.php" class="glass-button px-5 py-2.5 rounded-xl font-medium text-brand-300 hover:text-white flex items-center gap-2 group">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Administration
                    </a>
                <?php endif; ?>

                <a href="#" class="glass-button px-5 py-2.5 rounded-xl font-medium text-slate-300 hover:text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profil
                </a>
                <a href="logout.php" class="glass-button px-5 py-2.5 rounded-xl font-medium text-red-300 hover:text-red-200 hover:bg-red-500/10 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Déconnexion
                </a>
            </div>
        </div>

        <?php if (empty($events)): ?>
            <div class="glass-panel rounded-2xl p-12 text-center border-dashed border-2 border-slate-700">
                <div class="text-6xl mb-4">🎵</div>
                <h3 class="text-xl font-bold text-white mb-2">Aucun événement à venir</h3>
                <p class="text-slate-400">Profitez-en pour répéter vos gammes !</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <!-- Timeline line -->
                <!-- <div class="absolute left-8 top-0 bottom-0 w-px bg-slate-800 md:hidden"></div> -->

                <?php foreach ($events as $index => $event):
                    $date = new DateTime($event['date']);
                    // Using IntlDateFormatter for French dates
                    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                    $dateStr = $formatter->format($date);
                    $dayName = ucfirst($formatter->format(new DateTime($event['date'])));

                    // Style differentiation based on status
                    $borderClass = 'border-slate-700/50';
                    $bgClass = 'bg-slate-800/40';
                    if ($event['userStatus'] === 'PRESENT') {
                        $borderClass = 'border-green-500/30';
                        $bgClass = 'bg-green-500/5';
                    } elseif ($event['userStatus'] === 'ABSENT') {
                        $borderClass = 'border-red-500/30';
                        $bgClass = 'bg-red-500/5';
                    }
                ?>
                    <div class="glass-panel rounded-2xl p-6 md:p-8 border <?= $borderClass ?> <?= $bgClass ?> hover:border-brand-500/30 transition-all duration-300 group animate-slide-up" style="animation-delay: <?= $index * 100 ?>ms">
                        <div class="flex flex-col md:flex-row gap-6 md:items-center justify-between">

                            <!-- Date Badge & Info -->
                            <div class="flex gap-6 items-start">
                                <div class="hidden md:flex flex-col items-center justify-center w-20 h-20 rounded-xl bg-slate-800/80 border border-slate-700 text-center flex-shrink-0 shadow-lg">
                                    <span class="text-xs font-bold text-brand-400 uppercase tracking-wider"><?= $date->format('M') ?></span>
                                    <span class="text-3xl font-display font-bold text-white"><?= $date->format('d') ?></span>
                                    <span class="text-xs text-slate-400"><?= $date->format('D') ?></span>
                                </div>

                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <?php if ($event['isClosed']): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                                                Fermé
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-500/10 text-brand-300 border border-brand-500/20">
                                                Ouvert
                                            </span>
                                        <?php endif; ?>
                                        <div class="md:hidden text-brand-400 text-sm font-semibold capitalize">
                                            <?= $dateStr ?>
                                        </div>
                                    </div>

                                    <h2 class="text-2xl font-bold text-white mb-2 group-hover:text-brand-300 transition-colors">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h2>

                                    <div class="flex flex-wrap items-center gap-4 text-slate-400 text-sm">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-lg">⏰</span>
                                            <?= $date->format('H:i') ?>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-lg">📍</span>
                                            <?= htmlspecialchars($event['location']) ?>
                                        </div>
                                    </div>

                                    <?php if ($event['description']): ?>
                                        <p class="mt-4 text-slate-400 text-sm leading-relaxed max-w-2xl bg-slate-900/30 p-3 rounded-lg border border-white/5">
                                            <?= htmlspecialchars($event['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3 mt-2 md:mt-0 w-full md:w-auto flex-shrink-0">
                                <?php if ($event['isClosed']): ?>
                                    <div class="w-full md:w-auto px-6 py-3 rounded-xl bg-slate-800 text-slate-500 font-medium border border-slate-700 flex items-center justify-center gap-2 cursor-not-allowed opacity-75">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Inscriptions Closes
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="index.php" class="flex-1 md:flex-none">
                                        <input type="hidden" name="eventId" value="<?= $event['id'] ?>">
                                        <input type="hidden" name="status" value="PRESENT">
                                        <button
                                            type="submit"
                                            class="w-full md:w-32 px-4 py-3 rounded-xl font-bold transition-all transform active:scale-95 flex items-center justify-center gap-2 
                                            <?= $event['userStatus'] === 'PRESENT'
                                                ? 'bg-gradient-to-tr from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/30 ring-2 ring-white/20'
                                                : 'bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700' ?>">
                                            <?php if ($event['userStatus'] === 'PRESENT'): ?>
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            <?php endif; ?>
                                            Présent
                                        </button>
                                    </form>

                                    <form method="POST" action="index.php" class="flex-1 md:flex-none">
                                        <input type="hidden" name="eventId" value="<?= $event['id'] ?>">
                                        <input type="hidden" name="status" value="ABSENT">
                                        <button
                                            type="submit"
                                            class="w-full md:w-32 px-4 py-3 rounded-xl font-bold transition-all transform active:scale-95 flex items-center justify-center gap-2
                                            <?= $event['userStatus'] === 'ABSENT'
                                                ? 'bg-gradient-to-tr from-red-500 to-rose-600 text-white shadow-lg shadow-red-500/30 ring-2 ring-white/20'
                                                : 'bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700' ?>">
                                            <?php if ($event['userStatus'] === 'ABSENT'): ?>
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            <?php endif; ?>
                                            Absent
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>

</html>