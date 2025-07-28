<?php 

namespace App\Service;

use App\Core\Singleton;
use App\Repository\AchatRepository;
use App\Repository\CompteurRepository;

class AchatService extends Singleton{

    private AchatRepository $achatRepository;
    private CompteurRepository $compteurRepo;

    public function __construct(AchatRepository $achatRepository, CompteurRepository $compteurRepository){
        $this->achatRepository = $achatRepository;
        $this->compteurRepo = $compteurRepository;

    }

    public function getAllAchat(){
        return $this->achatRepository->selectAll();
    }

    public function enregistrerAchat(array $data): array
    {
        $validation = $this->validerDonnees($data);
        if (!$validation['valid']) {
            return [
                'statut' => 'error',
                'data' => null,
                'code' => 400,
                'message' => $validation['message']
            ];
        }

        return $this->achatRepository->enregistrerAchat($data);
    }

    private function validerDonnees(array $data): array
    {
        if (empty($data['numero_compteur'])) {
            return ['valid' => false, 'message' => 'Le numéro de compteur est requis'];
        }

        if (empty($data['montant']) || !is_numeric($data['montant'])) {
            return ['valid' => false, 'message' => 'Le montant doit être un nombre valide'];
        }

        if (floatval($data['montant']) <= 0) {
            return ['valid' => false, 'message' => 'Le montant doit être supérieur à 0'];
        }

        return ['valid' => true, 'message' => 'Données valides'];
    }


    public function verifierCompteur(string $numeroCompteur): array
    {
        try {
            $compteur = $this->compteurRepo->selectBy($numeroCompteur);
            

            if (!$compteur) {
                return [
                    'statut' => 'error',
                    'data' => null,
                    'code' => 404,
                    'message' => 'Le numéro de compteur n\'a pas été retrouvé'
                ];
            }

            return [
                'statut' => 'success',
                'data' => [
                    'numero_compteur' => $compteur['numero_compteur'],
                    'client' => trim($compteur['prenom'] . ' ' . $compteur['nom']),
                    'statut_compteur' => $compteur['statut'] ?? 'actif'
                ],
                'code' => 200,
                'message' => 'Compteur trouvé avec succès'
            ];
            
        } catch (\Exception $e) {
            return [
                'statut' => 'error',
                'data' => null,
                'code' => 500,
                'message' => 'Erreur lors de la vérification du compteur'
            ];
        }
    }

}