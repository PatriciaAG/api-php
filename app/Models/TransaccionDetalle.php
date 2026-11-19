<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
class TransaccionDetalle extends Model { protected string $table = 'transaccion_detalle'; protected string $primaryKey = 'id_transaccion_detalle'; protected array $fillable = ['id_transaccion','id_servicio','cantidad','precio_unitario','subtotal']; }
