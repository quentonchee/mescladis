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

<div class="min-h-screen bg-gray-900 text-white p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-600">
                    Bonjour, <?= htmlspecialchars($user['name'] ?: 'Musicien') ?> !
                </h1>
                <p class="text-gray-400 mt-1">Voici les événements à venir.</p>
            </div>
            <div class="flex gap-4">
                <?php
                // Check for admin role logic
                // Using simple legacy column check for seed admin, or we could join roles.
                // Seed admin has role='ADMIN'.
                $isAdmin = ($user['role'] === 'ADMIN');
                if ($isAdmin): ?>
                    <a href="admin/events.php" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded transition-colors text-sm font-bold flex items-center">
                        Administration
                    </a>
                <?php endif; ?>

                <a href="#" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors text-sm font-bold flex items-center">
                    Mon Profil
                </a>
                <a href="logout.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors text-sm flex items-center">
                    Se déconnecter
                </a>
            </div>
        </div>

        <?php if (empty($events)): ?>
            <div class="text-center py-12 bg-gray-800 rounded-lg border border-gray-700">
                <p class="text-xl text-gray-400">Aucun événement à venir.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($events as $event):
                    $date = new DateTime($event['date']);
                    // French date formatting manually or using Intl
                    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                    $dateStr = $formatter->format($date);
                ?>
                    <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 hover:border-gray-600 transition-all">
                        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-white mb-2"><?= htmlspecialchars($event['title']) ?></h2>
                                <div class="flex items-center text-gray-300 mb-1">
                                    <span class="mr-2">📅</span>
                                    <?= htmlspecialchars(ucfirst($dateStr)) ?>
                                </div>
                                <div class="flex items-center text-gray-400">
                                    <span class="mr-2">📍</span>
                                    <?= htmlspecialchars($event['location']) ?>
                                </div>
                                <?php if ($event['description']): ?>
                                    <p class="mt-3 text-gray-400 text-sm bg-gray-900/50 p-3 rounded">
                                        <?= htmlspecialchars($event['description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="flex gap-3 shrink-0">
                                <?php if ($event['isClosed']): ?>
                                    <div class="flex items-center justify-center bg-red-900/30 border border-red-900 text-red-400 px-6 py-3 rounded-lg font-bold">
                                        🔒 Inscriptions Closes
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="index.php" class="flex-1 md:flex-none">
                                        <input type="hidden" name="eventId" value="<?= $event['id'] ?>">
                                        <input type="hidden" name="status" value="PRESENT">
                                        <button
                                            type="submit"
                                            class="w-full px-6 py-3 rounded-lg font-bold transition-all transform active:scale-95 flex items-center justify-center gap-2 <?= $event['userStatus'] === 'PRESENT' ? 'bg-green-600 text-white ring-2 ring-green-400 shadow-[0_0_15px_rgba(34,197,94,0.5)]' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' ?>">
                                            <span>👍</span> Présent
                                        </button>
                                    </form>

                                    <form method="POST" action="index.php" class="flex-1 md:flex-none">
                                        <input type="hidden" name="eventId" value="<?= $event['id'] ?>">
                                        <input type="hidden" name="status" value="ABSENT">
                                        <button
                                            type="submit"
                                            class="w-full px-6 py-3 rounded-lg font-bold transition-all transform active:scale-95 flex items-center justify-center gap-2 <?= $event['userStatus'] === 'ABSENT' ? 'bg-red-600 text-white ring-2 ring-red-400 shadow-[0_0_15px_rgba(239,68,68,0.5)]' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' ?>">
                                            <span>👎</span> Absent
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