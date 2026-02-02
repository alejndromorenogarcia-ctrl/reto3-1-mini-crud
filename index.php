<?php
// index.php
session_start();

// Inicializar array de tareas
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

$message = '';
$messageType = ''; // success | error

// === CREAR / EDITAR TAREA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = $_POST['priority'] ?? 'Media';
    $id          = $_POST['id'] ?? '';

    if ($title === '') {
        $message = 'El título de la tarea no puede estar vacío.';
        $messageType = 'error';
    } else {
        if ($id === '') {
            // Crear nueva tarea
            $newId = time();
            $_SESSION['tasks'][$newId] = [
                'id'          => $newId,
                'title'       => $title,
                'description' => $description,
                'priority'    => $priority,
            ];
            $message = 'Tarea creada correctamente.';
            $messageType = 'success';
        } else {
            // Editar tarea existente
            if (isset($_SESSION['tasks'][$id])) {
                $_SESSION['tasks'][$id]['title']       = $title;
                $_SESSION['tasks'][$id]['description'] = $description;
                $_SESSION['tasks'][$id]['priority']    = $priority;
                $message = 'Tarea actualizada correctamente.';
                $messageType = 'success';
            } else {
                $message = 'La tarea que intentas editar no existe.';
                $messageType = 'error';
            }
        }
    }
}

// === BORRAR TAREA ===
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    if (isset($_SESSION['tasks'][$deleteId])) {
        unset($_SESSION['tasks'][$deleteId]);
        $message = 'Tarea eliminada correctamente.';
        $messageType = 'success';
    } else {
        $message = 'La tarea que intentas eliminar no existe.';
        $messageType = 'error';
    }
}

// === PREPARAR EDICIÓN ===
$editTask = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    if (isset($_SESSION['tasks'][$editId])) {
        $editTask = $_SESSION['tasks'][$editId];
    }
}

// Lista final de tareas (para la tabla)
$tasks = array_values($_SESSION['tasks']);

include 'includes/header.php';
?>

<div class="section">
    <h1>Mini CRUD de tareas (<?= count($tasks) ?> tareas)</h1>

    <?php if ($message): ?>
        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de nueva tarea / edición -->
    <h2>Nueva tarea</h2>

    <form method="post" class="task-form">
        <input type="hidden" name="id" value="<?= $editTask['id'] ?? '' ?>">

        <label for="title">Título</label>
        <input
            type="text"
            id="title"
            name="title"
            value="<?= isset($editTask['title']) ? htmlspecialchars($editTask['title'], ENT_QUOTES, 'UTF-8') : '' ?>"
            placeholder="Escribe el título de la tarea"
        >

        <label for="description">Descripción</label>
        <textarea
            id="description"
            name="description"
            rows="3"
            placeholder="Añade una descripción corta"
        ><?= isset($editTask['description']) ? htmlspecialchars($editTask['description'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>

        <label for="priority">Prioridad</label>
        <select id="priority" name="priority">
            <?php
            $currentPriority = $editTask['priority'] ?? 'Media';
            ?>
            <option value="Baja"  <?= $currentPriority === 'Baja'  ? 'selected' : '' ?>>Baja</option>
            <option value="Media" <?= $currentPriority === 'Media' ? 'selected' : '' ?>>Media</option>
            <option value="Alta"  <?= $currentPriority === 'Alta'  ? 'selected' : '' ?>>Alta</option>
        </select>

        <button type="submit" class="btn">
            <?= $editTask ? 'Guardar cambios' : 'Añadir tarea' ?>
        </button>

        <?php if ($editTask): ?>
            <a href="index.php" class="btn btn-secondary">Cancelar edición</a>
        <?php endif; ?>
    </form>

    <!-- Listado de tareas -->
    <h1>Lista del mini crud de las tareas</h1>

    <?php if (count($tasks) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Prioridad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['priority'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="index.php?edit=<?= urlencode($task['id']) ?>" class="btn btn-secondary">Editar</a>
                        <a
                            href="index.php?delete=<?= urlencode($task['id']) ?>"
                            class="btn btn-danger"
                            onclick="return confirm('¿Seguro que quieres eliminar esta tarea?');"
                        >Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay tareas todavía. Añade la primera usando el formulario de arriba.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
