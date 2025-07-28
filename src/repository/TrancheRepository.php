<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;

class TrancheRepository extends AbstractRepository {
    private string $table = 'tranche';

    public function __construct() {
        parent::__construct();
    }

    public function getTrancheByMontant(int $montant) {
        $sql = "SELECT * FROM $this->table 
                WHERE :montant BETWEEN borne_min AND borne_max 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['montant' => $montant]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
