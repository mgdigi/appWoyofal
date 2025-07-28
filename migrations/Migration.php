<?php

require_once  __DIR__ .  '/../vendor/autoload.php';
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


class Migration
{
    private static ?\PDO $pdo = null;
   
    private static function connect()
    {
        

        if (self::$pdo === null) {
          
            self::$pdo = new \PDO($_ENV['dsn'],
            $_ENV['DB_USER'],
              $_ENV['DB_PASSWORD']);
        }
    }

    private static function getQueries(): array {
    $driver = self::$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        return [ 
            "CREATE TABLE client (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL
            )",
            "CREATE TABLE compteur (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_compteur VARCHAR(50) NOT NULL UNIQUE,
                client_id INT NOT NULL,
                FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE
            )",
            "CREATE TABLE tranche (
            id INT AUTO_INCREMENT PRIMARY KEY,
            libelle VARCHAR(50),
            prix_kwt DECIMAL(10,2),
            borne_min DECIMAL(10,2),
            borne_max DECIMAL(10,2)
            )",
            "CREATE TABLE achat (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reference VARCHAR(100) NOT NULL UNIQUE,
                code VARCHAR(100) NOT NULL UNIQUE,
                date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                prix DECIMAL(10, 2) NOT NULL,
                nombre_kwt DECIMAL(10, 2) NOT NULL,
                client_id INT NOT NULL,
                compteur_id INT NOT NULL,
                tranche_id INT NOT NULL,
                FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE,
                FOREIGN KEY (compteur_id) REFERENCES compteur(id) ON DELETE CASCADE,
                FOREIGN KEY (tranche_id) REFERENCES tranche(id) ON DELETE CASCADE
            )",
            "CREATE TABLE journalisation (
                id INT AUTO_INCREMENT PRIMARY KEY,
                date DATE NOT NULL DEFAULT CURRENT_DATE,
                heure TIME NOT NULL DEFAULT CURRENT_TIME,
                localisation VARCHAR(255),
                ip_adresse VARCHAR(50),
                statut ENUM('Success', 'Échec') NOT NULL,
                client_id INT NOT NULL,
                compteur_id INT NOT NULL,
                achat_id INT NOT NULL,
               FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE,
               FOREIGN KEY (compteur_id) REFERENCES compteur(id) ON DELETE CASCADE,
               FOREIGN KEY (achat_id) REFERENCES achat(id) ON DELETE CASCADE
            )"
        ];
    } else {
        return [
            "CREATE TABLE client (
                id SERIAL PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL
            )",
            "CREATE TABLE compteur (
                id SERIAL PRIMARY KEY,
                numero_compteur VARCHAR(50) UNIQUE NOT NULL,
                client_id INTEGER NOT NULL,
                FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE
            )",
            "CREATE TABLE tranche (
                id SERIAL PRIMARY KEY,
                nom VARCHAR(50),
                prix_kwt DECIMAL(10,2),
                borne_min DECIMAL(10,2),
                borne_max DECIMAL(10,2)
            )",
            "CREATE TABLE achat (
                id SERIAL PRIMARY KEY,
                reference VARCHAR(100) UNIQUE NOT NULL,
                code VARCHAR(100) UNIQUE NOT NULL, 
                date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                tranche VARCHAR(50) NOT NULL,
                prix NUMERIC(10, 2) NOT NULL,
                nombre_kwt NUMERIC(10, 2) NOT NULL,
                client_id INTEGER NOT NULL,
                compteur_id INTEGER NOT NULL,
                tranche_id INTEGER NOT NULL,
                FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE,
                FOREIGN KEY (compteur_id) REFERENCES compteur(id) ON DELETE CASCADE,
                FOREIGN KEY (tranche_id) REFERENCES tranche(id) ON DELETE CASCADE
            )",
            "CREATE TABLE journalisation (
                id SERIAL PRIMARY KEY,
                date DATE NOT NULL DEFAULT CURRENT_DATE,
                heure TIME NOT NULL DEFAULT CURRENT_TIME,
                localisation VARCHAR(255),
                ip_adresse VARCHAR(50),
                statut VARCHAR(20) CHECK (statut IN ('Success', 'Échec')),
                client_id INT NOT NULL,
                compteur_id INT NOT NULL,
                achat_id INT NOT NULL,
               FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE,
               FOREIGN KEY (compteur_id) REFERENCES compteur(id) ON DELETE CASCADE,
               FOREIGN KEY (achat_id) REFERENCES achat(id) ON DELETE CASCADE
            )",
        ];
    }
}


    public static function up()
{
    self::connect();
    $queries = self::getQueries();

    foreach ($queries as $sql) {
        try {
            self::$pdo->exec($sql);
            echo "Requête exécutée avec succès.\n";
        } catch (PDOException $e) {
            echo "Erreur lors de l'exécution de la requête: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    echo "Migration terminée avec succès.\n";
}

}

Migration::up();