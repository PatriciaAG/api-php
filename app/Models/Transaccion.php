<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
class Transaccion extends Model { protected string $table = 'transaccion'; protected string $primaryKey = 'id_transaccion'; protected array $fillable = ['tr_fecha','tr_total_pagar','id_persona']; }
