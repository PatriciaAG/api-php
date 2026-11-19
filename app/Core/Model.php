<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey;
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC")->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->
        prepare(
            "SELECT * FROM {$this->table} 
             WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $data = $this->onlyFillable($data);
        if (!$data) throw new \InvalidArgumentException('No se recibieron campos válidos.');
        $columns = array_keys($data);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") 
                VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->onlyFillable($data);
        if (!$data) throw new \InvalidArgumentException('No se recibieron campos válidos para actualizar.');
        $sets = array_map(fn($column) => "$column = ?", array_keys($data));
        $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $sets) . 
                                    " WHERE {$this->primaryKey} = ?");
        $stmt->execute([...array_values($data), $id]);
        return $stmt->rowCount() > 0 || $this->find($id) !== false;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} 
                                    WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    private function onlyFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }
}
