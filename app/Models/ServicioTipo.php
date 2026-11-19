<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
class ServicioTipo extends Model { protected string $table = 'servicio_tipo'; protected string $primaryKey = 'id_servicio_tipo'; protected array $fillable = ['servicio_tipo_descripcion']; }
