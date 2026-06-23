<?php
require_once 'C:\xampp\htdocs\evospace\config\db.php';
require_once 'C:\xampp\htdocs\evospace\secciones\eventoscasi\models\NotificacionModel.php';

/**
 * Modelo de Eventos adaptado a la base de datos de EvoSpace.
 */
class EventoModel
{
    private PDO $db;
    private NotificacionModel $notificacionModel;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->notificacionModel = new NotificacionModel($pdo);
    }

    /**
     * Crea un nuevo evento y dispara la notificación automática.
     */
    public function crearEvento(array $data): int
    {
        $this->validar($data);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO eventos (titulo, fecha, hora, lugar, enlace_ubicacion, descripcion)
                 VALUES (:titulo, :fecha, :hora, :lugar, :enlace_ubicacion, :descripcion)"
            );
            $stmt->execute([
                ':titulo'           => $data['titulo'],
                ':fecha'            => $data['fecha'],
                ':hora'             => $data['hora'] ?? null,
                ':lugar'            => $data['lugar'] ?? null,
                ':enlace_ubicacion' => $data['enlace_ubicacion'] ?? null,
                ':descripcion'      => $data['descripcion'] ?? null,
            ]);

            $eventoId = (int) $this->db->lastInsertId();

            $this->guardarCursosDestino($eventoId, $data['ramas']);

            // Envía la notificación al modelo correspondiente
            $this->notificacionModel->enviarNotificacion($eventoId, $data['titulo'], $data['ramas']);

            $this->db->commit();
            return $eventoId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Edita un evento existente.
     */
    public function editarEvento(int $id, array $data): bool
    {
        $this->validar($data);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE eventos
                 SET titulo = :titulo, fecha = :fecha, hora = :hora,
                     lugar = :lugar, enlace_ubicacion = :enlace_ubicacion, descripcion = :descripcion
                 WHERE id_evento = :id"
            );
            $stmt->execute([
                ':titulo'           => $data['titulo'],
                ':fecha'            => $data['fecha'],
                ':hora'             => $data['hora'] ?? null,
                ':lugar'            => $data['lugar'] ?? null,
                ':enlace_ubicacion' => $data['enlace_ubicacion'] ?? null,
                ':descripcion'      => $data['descripcion'] ?? null,
                ':id'               => $id,
            ]);

            $this->db->prepare("DELETE FROM eventos_cursos WHERE id_evento = :id")->execute([':id' => $id]);
            $this->guardarCursosDestino($id, $data['ramas']);

            $this->db->prepare("DELETE FROM notificaciones WHERE id_evento = :id")->execute([':id' => $id]);
            $this->notificacionModel->enviarNotificacion($id, $data['titulo'], $data['ramas']);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un evento.
     */
    public function eliminarEvento(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM eventos WHERE id_evento = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Devuelve un evento por su ID_EVENTO.
     */
    public function obtenerEvento(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM eventos WHERE id_evento = :id");
        $stmt->execute([':id' => $id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evento) {
            return null;
        }

        $evento['ramas'] = $this->obtenerCursosDeEvento((int) $evento['id_evento']);
        return $evento;
    }

    /**
     * Devuelve todos los eventos ordenados por fecha.
     */
    public function obtenerEventos(): array
    {
        $stmt = $this->db->query("SELECT * FROM eventos ORDER BY fecha DESC, hora DESC");
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$eventos) {
            return [];
        }

        foreach ($eventos as &$evento) {
            if (isset($evento['id_evento'])) {
                $evento['ramas'] = $this->obtenerCursosDeEvento((int) $evento['id_evento']);
            }
        }

        return $eventos;
    }

    /**
     * Devuelve los próximos eventos.
     */
    public function obtenerProximosEventos(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha ASC, hora ASC LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$eventos) {
            return [];
        }

        foreach ($eventos as &$evento) {
            if (isset($evento['id_evento'])) {
                $evento['ramas'] = $this->obtenerCursosDeEvento((int) $evento['id_evento']);
            }
        }

        return $eventos;
    }

    // ===================== Helpers Internos Corregidos =====================

    private function validar(array $data): void
    {
        if (empty($data['titulo'])) {
            throw new InvalidArgumentException('El evento necesita un título.');
        }
        if (empty($data['fecha'])) {
            throw new InvalidArgumentException('El evento necesita una fecha.');
        }
        if (empty($data['ramas']) || !is_array($data['ramas'])) {
            throw new InvalidArgumentException('Debe seleccionarse al menos una opción destino.');
        }
    }

    private function guardarCursosDestino(int $eventoId, array $cursoIds): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO eventos_cursos (id_evento, id_curso) VALUES (:id_evento, :id_curso)"
        );
        foreach (array_unique($cursoIds) as $cursoId) {
            $stmt->execute([':id_evento' => $eventoId, ':id_curso' => (int) $cursoId]);
        }
    }

    private function obtenerCursosDeEvento(int $eventoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id, c.nombre FROM cursos c
             INNER JOIN eventos_cursos ec ON ec.id_curso = c.id
             WHERE ec.id_evento = :id_evento"
        );
        $stmt->execute([':id_evento' => $eventoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}