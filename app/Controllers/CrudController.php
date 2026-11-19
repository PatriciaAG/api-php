<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Model;
use App\Core\Response;
use Throwable;

class CrudController
{
    public function __construct(protected Model $model) {}

    public function index(): void { Response::json(['success' => true, 'data' => $this->model->all()]); }

    public function show(int $id): void
    {
        $row = $this->model->find($id);
        $row ? Response::json(['success' => true, 'data' => $row]) : Response::error('Registro no encontrado.', 404);
    }

    public function store(array $input): void
    {
        try {
            $id = $this->model->create($input);
            Response::json(['success' => true, 'message' => 'Registro creado.', 'id' => $id], 201);
        } catch (Throwable $e) { Response::error($e->getMessage(), 422); }
    }

    public function update(int $id, array $input): void
    {
        if (!$this->model->find($id)) { Response::error('Registro no encontrado.', 404); return; }
        try {
            $this->model->update($id, $input);
            Response::json(['success' => true, 'message' => 'Registro actualizado.']);
        } catch (Throwable $e) { Response::error($e->getMessage(), 422); }
    }

    public function destroy(int $id): void
    {
        try {
            $this->model->delete($id) ? Response::json(['success' => true, 'message' => 'Registro eliminado.']) : Response::error('Registro no encontrado.', 404);
        } catch (Throwable $e) { Response::error('No se puede eliminar: existen registros relacionados.', 409); }
    }
}
