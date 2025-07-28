<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;

class CompteurRepository extends AbstractRepository{

    private string $table = 'compteur';

    function __construct(){

        parent::__construct();
    }

    public  function selectBy(string $numero){

        $sql = "SELECT c.*, cl.nom, cl.prenom 
                FROM $this->table c 
                JOIN client cl ON c.client_id = cl.id 
                WHERE c.numero_compteur = :numero_compteur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'numero_compteur' => $numero
        ]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

     
    

}