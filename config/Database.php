<?php

// Ce fichier gère la connexion à MySQL pour tout le projet.
// Au lieu de se connecter dans chaque fichier séparément,
// on le fait une seule fois ici et on partage cette connexion partout.

class Database {

    // Ces informations correspondent à notre serveur MAMP local
    private static string $host     = "127.0.0.1";
    private static int    $port     = 8889;
    private static string $dbname   = "PadelManager";
    private static string $user     = "root";
    private static string $password = "root";

    // Cette variable garde la connexion en mémoire pour ne pas en créer plusieurs
    private static ?PDO $instance = null;

    // Retourne la connexion PDO.
    // Si elle n'existe pas encore, on la crée. Sinon on réutilise celle déjà ouverte.
    // C'est ce qu'on appelle le pattern "Singleton" : une seule instance en mémoire.
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::$instance = new PDO(
                "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname . ";charset=utf8mb4",
                self::$user,
                self::$password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // affiche les erreurs SQL
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // retourne des tableaux associatifs par défaut
                ]
            );
        }
        return self::$instance;
    }
}
