<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;

class ClientRepository extends AbstractRepository{

    private string $table = 'client';

    public function __construct(){
        parent::__construct();
    }

    public function findById(int $id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }


    

}