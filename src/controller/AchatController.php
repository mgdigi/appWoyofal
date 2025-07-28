<?php

namespace App\Controller;

use App\Core\App;
use App\Service\AchatService;
use App\Core\Abstract\AbstractController;

class AchatController  extends AbstractController
{
    private AchatService $achatService;

    public function __construct(AchatService $achatService)
    {
        $this->achatService = $achatService;
    }


    public function show(){
        try {
            $achats = $this->achatService->getAllAchat();
            $response = [
                'success' => true,
                'message' => 'Achat récupérés avec succès',
                'data' => $achats
            ];
            
            $this->renderJson($response, 200);
            
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des achats',
                'error' => $e->getMessage()
            ];
            
            $this->renderJson($response, httpCode: 500);
        }
    }

    public function index()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->renderJson([
                    'statut' => 'error',
                    'data' => null,
                    'code' => 405,
                    'message' => 'Méthode non autorisée. Utilisez POST.'
                ], 405);
                return;
            }

            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->renderJson([
                    'statut' => 'error',
                    'data' => null,
                    'code' => 400,
                    'message' => 'Format JSON invalide'
                ], 400);
                return;
            }

            $data['ip_adresse'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';

            $resultat = $this->achatService->enregistrerAchat($data);

            $httpCode = ($resultat['statut'] === 'success') ? 200 : $resultat['code'];

            $this->renderJson($resultat, $httpCode);

        } catch (\Exception $e) {
            $this->renderJson([
                'statut' => 'error',
                'data' => null,
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
        
    }

      public function verifierCompteur($params = []): void
    {
        try {
            $numeroCompteur = $params['numeroCompteur'] ?? null;
            
            if (!$numeroCompteur) {
                $response = [
                    'success' => false,
                    'message' => 'numero compteur requisquis'
                ];
                $this->renderJson($response, 400);
                return;
            }

            $compteur = $this->achatService->verifierCompteur($numeroCompteur);
            
            if (!$compteur) {
                $response = [
                    'success' => false,
                    'message' => 'compteur non trouvé'
                ];
                $this->renderJson($response, 404);
                return;
            }

            $response = [
                'success' => true,
                'message' => 'compteur récupéré avec succès',
                'data' => $compteur
            ];
            
            $this->renderJson($response, 200);
            
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Erreur lors de la récupération du compteur',
                'error' => $e->getMessage()
            ];
            
            $this->renderJson($response, 500);
        }
    }

}