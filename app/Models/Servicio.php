<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
class Servicio extends Model { protected string $table = 'servicio'; protected string $primaryKey = 'id_servicio'; protected array $fillable = ['servicios_nombre','servicios_descripcion','servicios_imagen','servicios_precio_unitario','id_servicio_tipo']; }
