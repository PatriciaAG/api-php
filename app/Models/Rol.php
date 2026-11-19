<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
class Rol extends Model { 
    protected string $table = 'rol'; 
    protected string $primaryKey = 'id_rol'; 
    protected array $fillable = ['rol_nombre']; }
