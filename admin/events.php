<?php
require_once '../src/utils/auth.php';
require_once '../src/models/Event.php';

require_admin();
$eventModel = new Event();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'title' => $_POST['title'],
            'date' => $_POST['date'],
            'location' => $_POST['location'],
            'description' => $_POST['description']
        ];
        $eventModel->create($data);
    } elseif ($action === 'delete') {
        $eventModel->delete($_POST['id']);
    } elseif ($action === 'update') {
        $data = [
            'title' => $_POST['title'],
            'date' => $_POST['date'],
            'location' => $_POST['location'],
            'description' => $_POST['description']
        ];
        $eventModel->update($_POST['id'], $data);
    }

    header("Location: events.php");
    exit;
}

$events = $eventModel->getAll();

// Include shared header (paths are relative to this file, so we need to step back)
// Note: header.php assumes it's at root for some things? No, it's just CDNs.
// BUT: header.php starts with <!DOCTYPE html>... and opens <body>.
// We need to be careful if we are in subdirectory.
// The header.php is located at ../templates/header.php from here.
// But wait, if we just include it, the HTML is output.
// Is there any issue? No.
include '../templates/header.php';
?>

<div class="relative min-h-screen pb-12">
    <!-- Decorative background elements -->
    <div class="fixed top-0 left-0 w-full h-96 bg-gradient-to-b from-brand-900/20 to-transparent pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 md:pt-12 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6 animate-slide-up">
            <div>
                <a href="../index.php" class="inline-flex items-center gap-2 text-slate-400 hover:text-brand-300 transition-colors mb-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour au tableau de bord
                </a>
                <h2 class="text-3xl md:text-4xl font-display font-bold text-white">Gestion des Événements</h2>
            </div>

            <button
                onclick="document.getElementById('createModal').classList.remove('hidden')"
                class="glass-button px-6 py-3 rounded-xl font-bold text-white bg-brand-600/20 hover:bg-brand-600/40 border-brand-500/30 hover:border-brand-500 flex items-center gap-2 shadow-lg shadow-brand-500/10">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter un Événement
            </button>
        </div>

        <div class="glass-panel rounded-2xl overflow-hidden border border-slate-700/50 shadow-xl animate-fade-in">
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-900/50 border-b border-white/5 text-slate-400 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="p-6 font-semibold">Titre</th>
                            <th class="p-6 font-semibold">Date</th>
                            <th class="p-6 font-semibold">Lieu</th>
                            <th class="p-6 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colSpan="4" class="p-12 text-center text-slate-400">
                                    <span class="text-4xl block mb-4">📅</span>
                                    Aucun événement trouvé.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr class="hover:bg-white/5 transition-colors group">
                                    <td class="p-6 font-medium text-white"><?= htmlspecialchars($event['title']) ?></td>
                                    <td class="p-6 text-slate-300"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($event['date']))) ?></td>
                                    <td class="p-6 text-slate-300"><?= htmlspecialchars($event['location']) ?></td>
                                    <td class="p-6">
                                        <div class="flex items-center justify-end gap-3 opacity-80 group-hover:opacity-100 transition-opacity">
                                            <button
                                                onclick='openEditModal(<?= json_encode($event) ?>)'
                                                class="text-blue-400 hover:text-blue-300 font-medium p-2 hover:bg-blue-500/10 rounded-lg transition-colors">
                                                Modifier
                                            </button>
                                            <form method="POST" onsubmit="return confirm('Supprimer cet événement ?');" class="inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                                <button type="submit" class="text-red-400 hover:text-red-300 font-medium p-2 hover:bg-red-500/10 rounded-lg transition-colors">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden divide-y divide-white/5">
                <?php foreach ($events as $event): ?>
                    <div class="p-5 hover:bg-white/5 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-lg font-bold text-white"><?= htmlspecialchars($event['title']) ?></span>
                            <div class="flex gap-2">
                                <button onclick='openEditModal(<?= json_encode($event) ?>)' class="text-blue-400 bg-blue-500/10 p-2 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <form method="POST" onsubmit="return confirm('Supprimer ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="text-red-400 bg-red-500/10 p-2 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 text-sm text-slate-400">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📅</span>
                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($event['date']))) ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📍</span>
                                <?= htmlspecialchars($event['location']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="document.getElementById('createModal').classList.add('hidden')"></div>
        <div class="glass-panel p-8 rounded-2xl w-full max-w-lg relative z-10 animate-slide-up border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white font-display">Créer un nouvel événement</h2>
            <form method="POST" class="space-y-5">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Titre</label>
                    <input type="text" name="title" required class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all placeholder-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Date et Heure</label>
                    <input type="datetime-local" name="date" required class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all calendar-picker-indicator-invert">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Lieu</label>
                    <input type="text" name="location" class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all placeholder-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Description</label>
                    <textarea name="description" rows="3" class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all placeholder-slate-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-medium">Annuler</button>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-brand-500/20 transition-all transform active:scale-95">Créer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <div class="glass-panel p-8 rounded-2xl w-full max-w-lg relative z-10 animate-slide-up border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white font-display">Modifier l'événement</h2>
            <form method="POST" class="space-y-5">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Titre</label>
                    <input type="text" name="title" id="edit_title" required class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Date et Heure</label>
                    <input type="datetime-local" name="date" id="edit_date" required class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Lieu</label>
                    <input type="text" name="location" id="edit_location" class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-300">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full p-3.5 rounded-xl bg-slate-800/50 border border-slate-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none text-white transition-all"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition-colors font-medium">Annuler</button>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-brand-500/20 transition-all transform active:scale-95">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(event) {
            document.getElementById('edit_id').value = event.id;
            document.getElementById('edit_title').value = event.title;
            const date = new Date(event.date);
            // Simple approach to get YYYY-MM-DDTHH:MM for local input
            // Removing seconds and timezone offset (ignoring complexity for now, assuming local usage)
            // Or better: keep it simple and just use the string if it's already in a good format.
            // But let's format safely.
            const dateString = date.toISOString().slice(0, 16);
            document.getElementById('edit_date').value = dateString;

            document.getElementById('edit_location').value = event.location || '';
            document.getElementById('edit_description').value = event.description || '';

            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>

    </body>

    </html>