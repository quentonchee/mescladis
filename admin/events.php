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
// Adjust paths for admin subdir
// Header is in ../templates/header.php, but it references src/utils/auth.php which is relative...
// Actually header.php only has HTML, but index.php includes it.
// Let's manually include header HTML here or fix paths.
?>
<!DOCTYPE html>
<html lang="fr" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Groupe de Musique - Admin</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>

<body class="bg-gray-900 text-white min-h-screen p-4 md:p-8">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold">Gestion des Événements</h2>
            <div class="flex gap-4">
                <a href="../index.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded font-bold transition-colors">
                    Retour
                </a>
                <button
                    onclick="document.getElementById('createModal').classList.remove('hidden')"
                    class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded font-bold transition-colors">
                    Ajouter un Événement
                </button>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-700">
            <table class="w-full text-left hidden md:table">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4">Titre</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Lieu</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colSpan="4" class="p-8 text-center text-gray-400">Aucun événement trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-750">
                                <td class="p-4 font-medium"><?= htmlspecialchars($event['title']) ?></td>
                                <td class="p-4"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($event['date']))) ?></td>
                                <td class="p-4"><?= htmlspecialchars($event['location']) ?></td>
                                <td class="p-4 flex gap-2">
                                    <button
                                        onclick='openEditModal(<?= json_encode($event) ?>)'
                                        class="text-blue-400 hover:text-blue-300 pointer">
                                        Modifier
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Supprimer cet événement ?');" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4 p-4">
                <?php foreach ($events as $event): ?>
                    <div class="bg-gray-750 p-4 rounded-lg border border-gray-600">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-lg font-bold text-white"><?= htmlspecialchars($event['title']) ?></span>
                            <div class="flex gap-2">
                                <button onclick='openEditModal(<?= json_encode($event) ?>)' class="text-blue-400 text-sm">Modifier</button>
                                <form method="POST" onsubmit="return confirm('Supprimer ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="text-red-400 text-sm">Supprimer</button>
                                </form>
                            </div>
                        </div>
                        <div class="text-sm text-gray-300 mb-1">📅 <?= htmlspecialchars($event['date']) ?></div>
                        <div class="text-sm text-gray-400">📍 <?= htmlspecialchars($event['location']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md border border-gray-700 shadow-2xl">
            <h2 class="text-2xl font-bold mb-4 text-white">Créer un nouvel événement</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Titre</label>
                    <input type="text" name="title" required class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Date et Heure</label>
                    <input type="datetime-local" name="date" required class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Lieu</label>
                    <input type="text" name="location" class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Description</label>
                    <textarea name="description" rows="3" class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white"></textarea>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 rounded text-gray-300 hover:bg-gray-700">Annuler</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded font-bold">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md border border-gray-700 shadow-2xl">
            <h2 class="text-2xl font-bold mb-4 text-white">Modifier l'événement</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Titre</label>
                    <input type="text" name="title" id="edit_title" required class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Date et Heure</label>
                    <input type="datetime-local" name="date" id="edit_date" required class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Lieu</label>
                    <input type="text" name="location" id="edit_location" class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white"></textarea>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded text-gray-300 hover:bg-gray-700">Annuler</button>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-6 py-2 rounded font-bold">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(event) {
            document.getElementById('edit_id').value = event.id;
            document.getElementById('edit_title').value = event.title;
            // Format date for datetime-local input (YYYY-MM-DDTHH:MM)
            const date = new Date(event.date);
            // Need to adjust for timezone locally or just use ISO string slice if stored as ISO
            // The previous app used slice(0, 16)
            document.getElementById('edit_date').value = event.date.slice(0, 16);
            document.getElementById('edit_location').value = event.location || '';
            document.getElementById('edit_description').value = event.description || '';

            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>

</body>

</html>