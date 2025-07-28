<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
class Seeder
{
    private static ?PDO $pdo = null;

    private static function connect()
    {
         if (self::$pdo === null) {
          
            self::$pdo = new \PDO($_ENV['dsn'],
            $_ENV['DB_USER'],
              $_ENV['DB_PASSWORD']);
        }
    }

    public static function run()
    {
        self::connect();

        try {
            $pdo = self::$pdo;

            $tranches = [
                ['nom' => 'Tranche 1', 'prix_kwt' => 91, 'borne_min' => 0, 'borne_max' => 150],
                ['nom' => 'Tranche 2', 'prix_kwt' => 102, 'borne_min' => 151, 'borne_max' => 250],
                ['nom' => 'Tranche 3', 'prix_kwt' => 116, 'borne_min' => 251, 'borne_max' => 400],
                ['nom' => 'Tranche 4', 'prix_kwt' => 132, 'borne_min' => 400, 'borne_max' => 40000]

            ];

            foreach ($tranches as $t) {
                $stmt = $pdo->prepare("INSERT INTO tranche (nom, prix_kwt, borne_min, borne_max) VALUES (?, ?, ?, ?)");
                $stmt->execute([$t['nom'], $t['prix_kwt'], $t['borne_min'], $t['borne_max']]);
                $trancheId = $pdo->lastInsertId();
            }
            echo "✅ Tranches insérées.\n";

            $stmt = $pdo->prepare("INSERT INTO client (nom, prenom) VALUES (?, ?)");
            $stmt->execute(['Gueye', 'Mohamed']);
            $clientId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO compteur (numero_compteur, client_id) VALUES (?, ?)");
            $stmt->execute(['SN-001-2025', $clientId]);
            $compteurId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO achat (reference, code, prix, nombre_kwt, client_id, compteur_id, tranche_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(['REF-001', 'CODE-ABC-123', 2500.00, 33.33, $clientId, $compteurId, $trancheId]);
            $achatId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO journalisation (localisation, ip_adresse, statut, client_id, compteur_id, achat_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Dakar', '192.168.1.100', 'Success', $clientId, $compteurId, $achatId]);

            echo "✅ Données de test insérées avec succès.\n";

        } catch (PDOException $e) {
            echo "❌ Erreur lors du seeding : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

Seeder::run();
