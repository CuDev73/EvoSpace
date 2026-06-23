<?php
class NotificacionModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Envía notificaciones a los cursos seleccionados (desactivado por ahora)
     */
    public function enviarNotificacion(int $eventoId, string $tituloEvento, array $cursoIds): void
    {
        return; // No hace nada, ya que la tabla notificaciones no se usa aún
    }

    public function obtenerNotificacionesPorCurso(int $cursoId): array
    {
        return [];
    }

    public function marcarComoLeida(int $notificacionId): bool
    {
        return true;
    }
}
?>