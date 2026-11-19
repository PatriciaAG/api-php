<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use PDO;
use Throwable;

class TransaccionController
{
    public function index(): void
    {
        $sql = 'SELECT t.*, CONCAT(p.persona_nombre, " ", p.persona_apellido) AS persona
                FROM transaccion t INNER JOIN persona p ON p.id_persona = t.id_persona
                ORDER BY t.id_transaccion DESC';
        Response::json(['success' => true, 'data' => Database::connection()->query($sql)->fetchAll()]);
    }

    public function show(int $id): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT t.*, p.persona_nombre, p.persona_apellido, p.persona_ci FROM transaccion t JOIN persona p ON p.id_persona=t.id_persona WHERE t.id_transaccion=?');
        $stmt->execute([$id]); $transaction = $stmt->fetch();
        if (!$transaction) { Response::error('Transacción no encontrada.', 404); return; }
        $stmt = $db->prepare('SELECT d.*, s.servicios_nombre FROM transaccion_detalle d JOIN servicio s ON s.id_servicio=d.id_servicio WHERE d.id_transaccion=?');
        $stmt->execute([$id]); $transaction['detalles'] = $stmt->fetchAll();
        Response::json(['success' => true, 'data' => $transaction]);
    }

    // Espera: {"id_persona":1,"detalles":[{"id_servicio":1,"cantidad":2}]}
    public function store(array $input): void
    {
        $personId = (int)($input['id_persona'] ?? 0);
        $details = $input['detalles'] ?? [];
        if ($personId < 1 || !is_array($details) || count($details) === 0) {
            Response::error('Debes indicar id_persona y al menos un detalle.'); return;
        }
        $db = Database::connection();
        try {
            $db->beginTransaction();
            $person = $db->prepare('SELECT id_persona FROM persona WHERE id_persona=?');
            $person->execute([$personId]);
            if (!$person->fetch()) throw new \InvalidArgumentException('La persona indicada no existe.');

            $total = 0.0; $prepared = [];
            $serviceQuery = $db->prepare('SELECT servicios_precio_unitario FROM servicio WHERE id_servicio=?');
            foreach ($details as $detail) {
                $serviceId = (int)($detail['id_servicio'] ?? 0);
                $quantity = (int)($detail['cantidad'] ?? 0);
                if ($serviceId < 1 || $quantity < 1) throw new \InvalidArgumentException('Cada detalle debe tener id_servicio y cantidad mayor a cero.');
                $serviceQuery->execute([$serviceId]); $service = $serviceQuery->fetch();
                if (!$service) throw new \InvalidArgumentException("No existe el servicio {$serviceId}.");
                $price = (float)$service['servicios_precio_unitario'];
                $subtotal = $price * $quantity; $total += $subtotal;
                $prepared[] = [$serviceId, $quantity, $price, $subtotal];
            }
            $stmt = $db->prepare('INSERT INTO transaccion (tr_fecha,tr_total_pagar,id_persona) VALUES (NOW(),?,?)');
            $stmt->execute([$total, $personId]); $transactionId = (int)$db->lastInsertId();
            $detailStmt = $db->prepare('INSERT INTO transaccion_detalle (id_transaccion,id_servicio,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?)');
            foreach ($prepared as [$serviceId,$quantity,$price,$subtotal]) $detailStmt->execute([$transactionId,$serviceId,$quantity,$price,$subtotal]);
            $db->commit();
            Response::json(['success' => true, 'message' => 'Transacción registrada.', 'id_transaccion' => $transactionId, 'total' => number_format($total, 2, '.', '')], 201);
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            Response::error($e->getMessage(), 422);
        }
    }
}
