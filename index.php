<?php
// ---------------------------------------------
// Configuración básica
// ---------------------------------------------
$dataFile = __DIR__ . '/data/tasks.json';

// Cargar tareas desde el fichero JSON
if (file_exists($dataFile)) {
    $json   = file_get_contents($dataFile);
    $tasks  = json_decode($json, true) ?? [];
} else {
    $tasks = [];
}

// Función para guardar tareas
function saveTasks($file, $tasks)
{
    file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ---------------------------------------------
// Lógica de acciones (crear / actualizar / borrar)
// ---------------------------------------------
$editTask = null;

// Crear o actualizar tarea (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    // CAMBIO PASO 3: prioridad
    $priority    = $_POST['priority'] ?? 'media';

    if ($title === '') {
        $errorMessage = 'El título es obligatorio.';
    } else {
        if ($id === null) {
            // Crear nueva tarea
            $newId = empty($tasks) ? 1 : (max(array_column($tasks, 'id')) + 1);

            $tasks[] = [
                'id'          => $newId,
                'title'       => $title,
                'description' => $description,
                'priority'    => $priority   // CAMBIO PASO 3
            ];
        } else {
            // Actualizar tarea existente
            foreach ($tasks as $index => $task) {
                if ($task['id'] === $id) {
                    $tasks[$index]['title']       = $title;
                    $tasks[$index]['description'] = $description;
                    $tasks[$index]['priority']    = $priority; // CAMBIO PASO 3
                    break;
                }
            }
        }

        saveTasks($dataFile, $tasks);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Eliminar tarea (GET ?delete=ID)
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $tasks = array_values(array_filter($tasks, fn($t) => $t['id'] !== $deleteId));
    saveTasks($dataFile, $tasks);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Preparar edición (GET ?edit=ID)
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($tasks as $task) {
        if ($task['id'] === $editId) {
            $editTask = $task;
            break;
        }
    }
}

// Contador de tareas (lo usabas en otro cambio, lo dejo ya aquí preparado)
$totalTasks = count($tasks);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mini CRUD de tareas</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Mini CRUD de tareas (<?php echo $totalTasks; ?> tareas)</h1>

        <?php if (!empty($errorMessage)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <section class="form-section">
            <h2><?php echo $editTask ? 'Editar tarea' : 'Nueva tarea'; ?></h2>

            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $editTask['id'] ?? ''; ?>">

                <div class="form-group">
                    <label for="title">Título</label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="<?php echo htmlspecialchars($editTask['title'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                    ><?php echo htmlspecialchars($editTask['description'] ?? ''); ?></textarea>
                </div>

                <!-- CAMBIO PASO 3: campo PRIORIDAD -->
                <div class="form-group">
                    <label for="priority">Prioridad</label>
                    <select name="priority" id="priority">
                        <?php
                        $currentPriority = $editTask['priority'] ?? 'media';
                        ?>
                        <option value="baja"  <?php echo $currentPriority === 'baja'  ? 'selected' : ''; ?>>Baja</option>
                        <option value="media" <?php echo $currentPriority === 'media' ? 'selected' : ''; ?>>Media</option>
                        <option value="alta"  <?php echo $currentPriority === 'alta'  ? 'selected' : ''; ?>>Alta</option>
                    </select>
                </div>
                <!-- FIN CAMBIO PASO 3 -->

                <button type="submit">
                    <?php echo $editTask ? 'Guardar cambios' : 'Añadir tarea'; ?>
                </button>
            </form>
        </section>

        <section class="list-section">
            <h2>Listado de tareas</h2>

            <?php if (empty($tasks)): ?>
                <p>No hay tareas todavía.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Prioridad</th> <!-- CAMBIO PASO 3 -->
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['description']); ?></td>
                                <!-- CAMBIO PASO 3: mostrar prioridad -->
                                <td><?php echo htmlspecialchars($task['priority'] ?? 'media'); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $task['id']; ?>">Editar</a>
                                    |
                                    <a href="?delete=<?php echo $task['id']; ?>"
                                       onclick="return confirm('¿Seguro que quieres eliminar esta tarea?');">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
<?php include 'includes/header.php'; ?>