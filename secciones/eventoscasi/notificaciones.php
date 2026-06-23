<?php
require_once 'C:\xampp\htdocs\evospace\config\db.php';

/**
 * Modelo de Notificaciones adaptado a la base de datos de EvoSpace.
 */
class NotificacionModel
{
    private PDO $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Genera alertas automáticas en la base de datos para los cursos seleccionados.
     */
    public function enviarNotificacion(int $eventoId, string $tituloEvento, array $cursoIds): void
    {
        // Creamos un mensaje genérico llamativo para la notificación
        $mensaje = "Se ha programado el evento: '" . $tituloEvento . "'. Revisá el panel de eventos para más detalles.";

        // CORREGIDO: Usamos las columnas exactas del PDF (id_evento, titulo, mensaje, tipo, id_curso)
        $sql = "INSERT INTO notificaciones (id_evento, titulo, mensaje, tipo, id_curso) 
                VALUES (:id_evento, :titulo, :mensaje, :tipo, :id_curso)";
        
        $stmt = $this->db->prepare($sql);

        // Recorremos cada curso seleccionado para generarle su notificación individual
        foreach (array_unique($cursoIds) as $cursoId) {
            $stmt->execute([
                ':id_evento' => $eventoId, // <-- Ahora vincula correctamente con id_evento
                ':titulo'    => 'Nuevo Evento Programado',
                ':mensaje'   => $mensaje,
                ':tipo'      => 'evento',
                ':id_curso'  => (int) $cursoId // <-- Vincula con id_curso
            ]);
        }
    }

    /**
     * Obtiene las notificaciones activas de un curso específico.
     */
    public function obtenerNotificacionesPorCurso(int $cursoId): array
    {
        $sql = "SELECT * FROM notificaciones WHERE id_curso = :id_curso ORDER BY fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_curso' => $cursoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marca una notificación como leída.
     */
    public function marcarComoLeida(int $notificacionId): bool
    {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $notificacionId]);
    }
}