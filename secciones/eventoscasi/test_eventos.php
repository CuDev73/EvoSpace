<?php
/**
 * Pruebas básicas del módulo Eventos y Notificaciones.
 *
 * Cómo ejecutar:
 * php test/test_eventos.php
 */

// 1. Importamos la base de datos primero para tener disponible el objeto $pdo
require_once  'C:\xampp\htdocs\evospace\config\db.php';
require_once  'C:\xampp\htdocs\evospace\secciones\eventoscasi\models\EventoModel.php';
require_once  'C:\xampp\htdocs\evospace\secciones\eventoscasi\models\NotificacionModel.php';

function assertTrue(bool $condicion, string $mensaje): void
{
    echo ($condicion ? "OK    " : "FALLÓ ") . "- $mensaje\n";
}

// 2. Le pasamos el objeto $pdo a los constructores
$eventoModel = new EventoModel($pdo);
$notificacionModel = new NotificacionModel($pdo);

echo "=== Pruebas del módulo Eventos y Notificaciones ===\n\n";

// 1. Crear un evento dirigido a Curso Superior (1) y Acrotelas (3)
$idEvento = $eventoModel->crearEvento([
    'titulo'      => 'Sesión de fotos para la obra',
    'fecha'       => '2026-08-13',
    'hora'        => '15:00',
    'lugar'       => 'Avda. Lalaland c/12 de junio',
    'descripcion' => 'Llevar polleras, sombreros y utilería',
    'color'       => '#8B1A1A',
    'ramas'       => [1, 3], // Cambiado a 'ramas' para que coincida con el validar() de tu EventoModel
]);
assertTrue($idEvento > 0, "Evento creado con ID $idEvento");

// 2. El evento debe existir y tener los 2 cursos guardados
$evento = $eventoModel->obtenerEvento($idEvento);
assertTrue($evento !== null, 'El evento se puede recuperar por ID');
assertTrue(count($evento['ramas']) === 2, 'El evento tiene 2 cursos destino');

// 3. Deben haberse generado notificaciones automáticas (una por curso)
$notificaciones = $notificacionModel->obtenerNotificaciones();
$notisDelEvento = array_filter($notificaciones, fn($n) => (int) $n['id_evento'] === $idEvento);
assertTrue(count($notisDelEvento) === 2, 'Se generaron 2 notificaciones automáticas (una por curso)');

// 4. Editar el evento, dejándolo solo para Curso Infantil (2)
$eventoModel->editarEvento($idEvento, [
    'titulo' => 'Sesión de fotos para la obra (reprogramada)',
    'fecha'  => '2026-08-20',
    'ramas'  => [2], // Cambiado a 'ramas'
]);
$eventoEditado = $eventoModel->obtenerEvento($idEvento);
assertTrue($eventoEditado['titulo'] === 'Sesión de fotos para la obra (reprogramada)', 'El título se actualizó');
assertTrue(count($eventoEditado['ramas']) === 1, 'Ahora el evento tiene solo 1 curso destino');

// 5. Las notificaciones viejas deben haberse reemplazado por una nueva
$notificacionesActualizadas = $notificacionModel->obtenerNotificaciones();
$notisActuales = array_filter($notificacionesActualizadas, fn($n) => (int) $n['id_evento'] === $idEvento);
assertTrue(count($notisActuales) === 1, 'Las notificaciones se regeneraron (ahora hay 1, no 2)');

// 6. Marcar como leída la notificación restante
$primera = array_values($notisActuales)[0] ?? null;
if ($primera) {
    $marcada = $notificacionModel->marcarLeida((int) $primera['id_notificacion']);
    assertTrue($marcada, 'Notificación marcada como leída sin errores');
}

// 7. Validación: crear evento sin curso debe fallar
try {
    $eventoModel->crearEvento(['titulo' => 'Evento sin curso', 'fecha' => '2026-09-01', 'ramas' => []]);
    assertTrue(false, 'Debería haber lanzado una excepción por falta de cursos');
} catch (InvalidArgumentException $e) {
    assertTrue(true, 'Rechaza correctamente un evento sin cursos (' . $e->getMessage() . ')');
}

// 8. Validación: crear evento sin título debe fallar
try {
    $eventoModel->crearEvento(['fecha' => '2026-09-01', 'ramas' => [1]]);
    assertTrue(false, 'Debería haber lanzado una excepción por falta de título');
} catch (InvalidArgumentException $e) {
    assertTrue(true, 'Rechaza correctamente un evento sin título (' . $e->getMessage() . ')');
}

// 9. Eliminar el evento de prueba (las notificaciones se borran en cascada)
$eliminado = $eventoModel->eliminarEvento($idEvento);
assertTrue($eliminado, 'Evento eliminado correctamente');

$eventoBorrado = $eventoModel->obtenerEvento($idEvento);
assertTrue($eventoBorrado === null, 'El evento ya no existe tras eliminarlo');

$notificacionesFinales = $notificacionModel->obtenerNotificaciones();
$notisHuerfanas = array_filter($notificacionesFinales, fn($n) => (int) $n['id_evento'] === $idEvento);
assertTrue(count($notisHuerfanas) === 0, 'Las notificaciones del evento se eliminaron en cascada');

echo "\n=== Pruebas finalizadas ===\n";