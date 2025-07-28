<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;

class JournalisationRepository extends AbstractRepository {
    private string $table = 'journalisation';

    public function __construct() {
        parent::__construct();
    }

    public function insert(array $data) {
        $sql = "INSERT INTO $this->table (date, heure, localisation, ip_adresse, statut, client_id, compteur_id, achat_id)
                VALUES (:date, :heure, :localisation, :ip_adresse, :statut, :client_id, :compteur_id, :achat_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
}
