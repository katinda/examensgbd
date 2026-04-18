<?php

// --- Paramètres de connexion à la base de données ---
$host     = "127.0.0.1"; // Adresse du serveur MySQL (127.0.0.1 pour forcer TCP avec MAMP)
$port     = 8889;         // Port MySQL de MAMP (différent du port standard 3306)
$dbname   = "PadelManager"; // Nom de la base de données
$user     = "root";       // Nom d'utilisateur MySQL
$password = "root";       // Mot de passe MySQL (par défaut "root" sur MAMP)

try {
    // Création de la connexion PDO
    // PDO (PHP Data Objects) est l'interface PHP pour communiquer avec MySQL
    // charset=utf8mb4 : support des caractères spéciaux et emojis
    // ERRMODE_EXCEPTION : toute erreur SQL lève une exception (plus facile à déboguer)
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Affichage des informations du serveur connecté
    echo "✅ Connexion réussie !\n";
    echo "   Serveur  : " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . "\n";
    echo "   Version  : " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo "   Base     : $dbname\n\n";

    // Récupération de la liste des tables présentes dans la base
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        // La base existe mais le schéma n'a pas encore été importé
        echo "⚠️  Aucune table trouvée.\n";
        echo "   → Importez le schéma : mysql -u root < padel_manager.sql\n";
    } else {
        // Affichage de toutes les tables trouvées
        echo "Tables trouvées (" . count($tables) . ") :\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
        echo "\n✅ Schéma présent et lié à la base.\n";
    }

} catch (PDOException $e) {
    // En cas d'échec de connexion : affichage du message d'erreur et conseils
    echo "❌ Erreur de connexion : " . $e->getMessage() . "\n\n";
    echo "Vérifiez :\n";
    echo "  - MySQL est démarré\n";
    echo "  - Le nom de la base ($dbname) existe\n";
    echo "  - USER / PASSWORD dans ce fichier\n";
}
