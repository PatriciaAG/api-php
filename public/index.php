<?php
declare(strict_types=1);

use App\Core\Response;
use App\Models\Rol;
use App\Models\Persona;
use App\Models\ServicioTipo;
use App\Models\Servicio;
use App\Models\TransaccionDetalle;
use App\Controllers\CrudController;
use App\Controllers\TransaccionController;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    }
});

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    if ($path === '/' || $path === '/api') {
        Response::json(['success' => true, 'message' => 'API de Gestión de Servicios', 'version' => '1.0', 'endpoints' => ['/api/roles','/api/personas','/api/servicio-tipos','/api/servicios','/api/transacciones','/api/transaccion-detalles']]);
        exit;
    }

    if (preg_match('#^/api/transacciones(?:/(\d+))?$#', $path, $matches)) {
        $controller = new TransaccionController();
        if ($method === 'GET' && empty($matches[1])) $controller->index();
        elseif ($method === 'GET') $controller->show((int)$matches[1]);
        elseif ($method === 'POST' && empty($matches[1])) $controller->store($input);
        else Response::error('Método no permitido para este endpoint.', 405);
        exit;
    }

    $resources = [
        'roles' => Rol::class,
        'personas' => Persona::class,
        'servicio-tipos' => ServicioTipo::class,
        'servicios' => Servicio::class,
        'transaccion-detalles' => TransaccionDetalle::class,
    ];
    if (preg_match('#^/api/([a-z-]+)(?:/(\d+))?$#', $path, $matches) && isset($resources[$matches[1]])) {
        $class = $resources[$matches[1]];
        $controller = new CrudController(new $class());
        $id = isset($matches[2]) ? (int)$matches[2] : null;
        if ($method === 'GET' && $id === null) $controller->index();
        elseif ($method === 'GET') $controller->show($id);
        elseif ($method === 'POST' && $id === null) $controller->store($input);
        elseif (in_array($method, ['PUT','PATCH'], true) && $id !== null) $controller->update($id, $input);
        elseif ($method === 'DELETE' && $id !== null) $controller->destroy($id);
        else Response::error('Método o recurso no permitido.', 405);
        exit;
    }
    Response::error('Endpoint no encontrado.', 404);
} catch (Throwable $e) {
    Response::error('Error interno del servidor.', 500, ['detail' => $e->getMessage()]);
}
