<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
class Persona extends Model { 
    protected string $table = 'persona'; 
    protected string $primaryKey = 'id_persona'; 
    protected array $fillable = 
    ['persona_nombre',
    'persona_apellido',
    'persona_ci',
    'id_rol']; 
}
