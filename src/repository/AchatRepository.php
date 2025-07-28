<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
class AchatRepository extends AbstractRepository{

    private string $table = 'achat';
    private JournalisationRepository $journalRepo;
    private CompteurRepository $compteurRepo;
    private ClientRepository $clientRepo;

    private TrancheRepository $trancheRepo;



    public function __construct(
        JournalisationRepository $journalisationRepository,
        CompteurRepository $compteurRepository,
        ClientRepository $clientRepository,
        TrancheRepository $trancheRepository

        
        ){

        parent::__construct();
        $this->journalRepo = $journalisationRepository;
        $this->compteurRepo = $compteurRepository;
        $this->clientRepo = $clientRepository;
       $this->trancheRepo = $trancheRepository;

    }

   public function selectAll():array{
     try{
        $sql = "SELECT * FROM $this->table";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
     
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
     }catch (\Exception $e) {
        throw new \PDOException($e->getMessage());
     }
    }

    public function insert(array $data) {
        $sql = "INSERT INTO $this->table (reference, code, date, prix, nombre_kwt, client_id, compteur_id, tranche_id)
                VALUES (:reference, :code, :date, :prix, :nombre_kwt, :client_id, :compteur_id, :tranche_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }


    public function enregistrerAchat(array $payload): array
    {
        $this->pdo->beginTransaction();
        try {
            // Vérifier l'existence du compteur
            $compteur = $this->compteurRepo->selectBy($payload['numero_compteur']);
            
            if (!$compteur) {
                throw new \Exception("Le numéro de compteur n'a pas été retrouvé");
            }

            // Récupérer le client associé
            $client = $this->clientRepo->findById($compteur['client_id']);
            if (!$client) {
                throw new \Exception("Client associé au compteur non trouvé");
            }

            // Calculer la tranche et les KWT en fonction du montant
            $montant = floatval($payload['montant']);
            $calculTranche = $this->calculerTranche($montant);

            // Générer référence et code unique
            $reference = $this->genererReference();
            $code = $this->genererCodeRecharge();

            $achatData = [
                'reference' => $reference,
                'code' => $code,
                'date' => date('Y-m-d H:i:s'),
                'prix' => $calculTranche['prix_kwh'],
                'nombre_kwt' => $calculTranche['kwt'],
                'client_id' => $client['id'],
                'compteur_id' => $compteur['id'],
                'tranche_id' => $calculTranche['tranche']
            ];

            if (!$this->insert($achatData)) {
                throw new \Exception("Erreur lors de l'enregistrement de l'achat");
            }

            $achat_id = $this->pdo->lastInsertId();

            $this->journaliserOperation([
                'numero_compteur' => $payload['numero_compteur'],
                'client_nom' => $client['nom'],
                'client_prenom' => $client['prenom'],
                'montant_demande' => $montant,
                'statut' => 'Success',
                'code_achat' => $code,
                'localisation' => $payload['localisation'] ?? null,
                'ip_adresse' => $payload['ip_adresse'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'client_id' => $client['id'],
                'compteur_id' => $compteur['id'],
                'achat_id' => $achat_id
            ]);

            $this->pdo->commit();

            return [
                'statut' => 'Success',
                'data' => [
                    'compteur' => $payload['numero_compteur'],
                    'reference' => $reference,
                    'code' => $code,
                    'date' => date('Y-m-d H:i:s'),
                    'tranche' => $calculTranche['tranche'],
                    'prix' => $calculTranche['prix_kwh'],
                    'nbreKwt' => $calculTranche['kwt'],
                    'client' => trim($client['prenom'] . ' ' . $client['nom'])
                ],
                'code' => 200,
                'message' => 'Achat effectué avec succès'
            ];

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            
            // TODO: Fix journalisation on error - needs valid client_id, compteur_id, achat_id
            // $this->journaliserOperation([...]);

            return [
                'statut' => 'error',
                'data' => null,
                'code' => 404,
                'message' => $e->getMessage()
            ];
        }
    }

    private function calculerTranche(float $montant): array
    {
        $tranches = [
            13 => ['borne_min' => 0, 'borne_max' => 150, 'prix_kwh' => 91],     
            14 => ['borne_min' => 151,'borne_max' => 250, 'prix_kwh' => 102],  
            15 => ['borne_min' => 251, 'borne_max' => 400, 'prix_kwh' => 116], 
            16 => ['borne_min' => 400, 'borne_max' => 40000, 'prix_kwh' => 132]
        ];

        foreach ($tranches as $numero => $tranche) {
            if ($montant >= $tranche['borne_min'] && $montant <= $tranche['borne_max']) {
                $kwt = round($montant / $tranche['prix_kwh'], 2);
                return [
                    'tranche' => $numero,
                    'prix_kwh' => $tranche['prix_kwh'],
                    'kwt' => $kwt
                ];
            }
        }

        return [
            'tranche' => 13,
            'prix_kwh' => 91,
            'kwt' => round($montant / 91, 2)
        ];
    }

    private function genererReference(): string
    {
        return 'REF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function genererCodeRecharge(): string
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    private function journaliserOperation(array $data): void
    {
        $journalData = [
            'date' => date('Y-m-d'),
            'heure' => date('H:i:s'),
            'localisation' => $data['localisation'],
            'ip_adresse' => $data['ip_adresse'] ?? null,
            'statut' => $data['statut'],
            'client_id' => $data['client_id'] ?? null,
            'compteur_id' => $data['compteur_id'] ?? null,
            'achat_id' => $data['achat_id'] ?? null
        ];

        $this->journalRepo->insert($journalData);
    }



}