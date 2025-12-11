<?php
require_once '../src/utils/auth.php';
require_once '../src/models/Role.php';

require_admin();
$roleModel = new Role();

$availablePermissions = [
    "MANAGE_USERS",
    "MANAGE_ROLES",
    "MANAGE_EVENTS",
    "VIEW_ADMIN",
    "VIEW_ATTENDANCE",
];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $permissions = isset($_POST['permissions']) ? json_encode($_POST['permissions']) : json_encode([]);
        $roleModel->create($_POST['name'], $permissions);
    } elseif ($action === 'delete') {
        $roleModel->delete($_POST['id']);
    } elseif ($action === 'update') {
        $permissions = isset($_POST['permissions']) ? json_encode($_POST['permissions']) : json_encode([]);
        $roleModel->update($_POST['id'], $_POST['name'], $permissions);
    }

    header("Location: roles.php");
    exit;
}

$roles = $roleModel->getAll();
?>
<!DOCTYPE html>
<html lang="fr" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Groupe de Musique - Admin Roles</title>
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
            <h2 class="text-3xl font-bold">Gestion des Rôles</h2>
            <div class="flex gap-4">
                <a href="../index.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded font-bold transition-colors">
                    Retour
                </a>
                <button
                    onclick="document.getElementById('createModal').classList.remove('hidden')"
                    class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded font-bold transition-colors">
                    Créer un Rôle
                </button>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-700">
            <table class="w-full text-left hidden md:table">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4">Nom</th>
                        <th class="p-4">Permissions</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colSpan="3" class="p-8 text-center text-gray-400">Aucun rôle trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roles as $role):
                            $perms = json_decode($role['permissions'], true) ?? [];
                        ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-750">
                                <td class="p-4 font-bold"><?= htmlspecialchars($role['name']) ?></td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($perms as $perm): ?>
                                            <span class="px-2 py-0.5 rounded text-xs bg-gray-700 text-gray-300 border border-gray-600">
                                                <?= htmlspecialchars($perm) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="p-4 flex items-center gap-3">
                                    <button
                                        onclick='openEditModal(<?= json_encode($role) ?>)'
                                        class="text-blue-400 hover:text-blue-300">
                                        Modifier
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Supprimer ce rôle ?');" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $role['id'] ?>">
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
                <?php foreach ($roles as $role):
                    $perms = json_decode($role['permissions'], true) ?? [];
                ?>
                    <div class="bg-gray-750 p-4 rounded-lg border border-gray-600">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-lg font-bold text-white"><?= htmlspecialchars($role['name']) ?></span>
                            <div class="flex gap-2">
                                <button onclick='openEditModal(<?= json_encode($role) ?>)' class="text-blue-400 text-sm">Modifier</button>
                                <form method="POST" onsubmit="return confirm('Supprimer ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                    <button type="submit" class="text-red-400 text-sm">Supprimer</button>
                                </form>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1 mb-2">
                            <?php foreach ($perms as $perm): ?>
                                <span class="px-2 py-0.5 rounded text-xs bg-gray-700 text-gray-300 border border-gray-600">
                                    <?= htmlspecialchars($perm) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md border border-gray-700 shadow-2xl overflow-y-auto max-h-[90vh]">
            <h2 class="text-2xl font-bold mb-4 text-white">Créer un nouveau rôle</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Nom du Rôle</label>
                    <input type="text" name="name" required class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Permissions</label>
                    <div class="grid grid-cols-1 gap-2">
                        <?php foreach ($availablePermissions as $perm): ?>
                            <label class="flex items-center space-x-2 bg-gray-700 p-2 rounded cursor-pointer hover:bg-gray-600">
                                <input type="checkbox" name="permissions[]" value="<?= $perm ?>" class="form-checkbox h-5 w-5 text-purple-600 rounded focus:ring-purple-500 border-gray-500 bg-gray-600">
                                <span class="text-sm text-gray-300"><?= $perm ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 rounded text-gray-300 hover:bg-gray-700">Annuler</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded font-bold">Créer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md border border-gray-700 shadow-2xl overflow-y-auto max-h-[90vh]">
            <h2 class="text-2xl font-bold mb-4 text-white">Modifier le rôle</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-300">Nom du Rôle</label>
                    <input type="text" name="name" id="edit_name" required class="w-full p-2 rounded bg-gray-700 border border-gray-600 focus:border-purple-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Permissions</label>
                    <div class="grid grid-cols-1 gap-2">
                        <?php foreach ($availablePermissions as $perm): ?>
                            <label class="flex items-center space-x-2 bg-gray-700 p-2 rounded cursor-pointer hover:bg-gray-600">
                                <input type="checkbox" name="permissions[]" value="<?= $perm ?>" id="edit_perm_<?= $perm ?>" class="form-checkbox h-5 w-5 text-purple-600 rounded focus:ring-purple-500 border-gray-500 bg-gray-600">
                                <span class="text-sm text-gray-300"><?= $perm ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded text-gray-300 hover:bg-gray-700">Annuler</button>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-6 py-2 rounded font-bold">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(role) {
            document.getElementById('edit_id').value = role.id;
            document.getElementById('edit_name').value = role.name;

            // Reset checkboxes
            document.querySelectorAll('input[id^="edit_perm_"]').forEach(cb => cb.checked = false);

            // Set checked
            try {
                const perms = JSON.parse(role.permissions);
                perms.forEach(perm => {
                    const el = document.getElementById('edit_perm_' + perm);
                    if (el) el.checked = true;
                });
            } catch (e) {}

            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>

</body>

</html>