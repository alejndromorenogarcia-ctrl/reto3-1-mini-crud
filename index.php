<?php
session_start();

function load_tasks() {
    $file = __DIR__ . '/data/tasks.json';
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_tasks($tasks) {
    $file = __DIR__ . '/data/tasks.json';
    file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$tasks = load_tasks();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '') {
            $message = 'El título es obligatorio para crear una tarea.';
        } else {
            $newId = time();
            $tasks[] = [
                'id' => $newId,
                'title' => $title,
                'description' => $description
            ];
            save_tasks($tasks);
            $message = 'Tarea creada correctamente.';
        }
    }

    if ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id !== null) {
            foreach ($tasks as &$task) {
                if ((string)$task['id'] === (string)$id) {
                    $task['title'] = $title;
                    $task['description'] = $description;
                    $message = 'Tarea actualizada correctamente.';
                    break;
                }
            }
            unset($task);
            save_tasks($tasks);
        }
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id !== null) {
            $tasks = array_values(array_filter($tasks, function ($task) use ($id) {
                return (string)$task['id'] !== (string)$id;
            }));
            save_tasks($tasks);
            $message = 'Tarea eliminada correctamente.';
        }
    }

    // Recargar para evitar reenvío de formularios al refrescar
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$tasks = load_tasks();
$editTask = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($tasks as $task) {
        if ((string)$task['id'] === (string)$editId) {
            $editTask = $task;
            break;
        }
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <?php $totalTasks = count($tasks); ?>







    <h1>Mini CRUD de Tareas (<?php echo $totalTasks; ?> tareas)</h1>

    <p class="subtitle">
        Este proyecto simula un CRUD sencillo usando PHP y un archivo JSON como "almacén" de datos.
    </p>

    <?php if (!empty($message)): ?>
        <div class="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="crud-layout">
        <div class="crud-column">
            <h2><?php echo $editTask ? 'Editar tarea' : 'Crear nueva tarea'; ?></h2>
            <form method="post" class="card">
                <input type="hidden" name="action" value="<?php echo $editTask ? 'update' : 'create'; ?>">
                <?php if ($editTask): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editTask['id'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>

                <label for="title">Título</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    required
                    value="<?php echo $editTask ? htmlspecialchars($editTask['title'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                >

                <label for="description">Descripción</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                ><?php echo $editTask ? htmlspecialchars($editTask['description'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>

                <button type="submit" class="btn primary">
                    <?php echo $editTask ? 'Guardar cambios' : 'Crear tarea'; ?>
                </button>

                <?php if ($editTask): ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn secondary block">Cancelar edición</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="crud-column">
            <h2>Listado de tareas</h2>

            <?php if (empty($tasks)): ?>
                <p>No hay tareas todavía. Crea la primera usando el formulario de la izquierda.</p>
            <?php else: ?>
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td class="col-actions">
                                    <a href="?edit=<?php echo urlencode($task['id']); ?>" class="btn small">Editar</a>

                                    <form method="post" class="inline-form" onsubmit="return confirm('¿Seguro que quieres eliminar esta tarea?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn small danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
