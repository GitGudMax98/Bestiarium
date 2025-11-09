<?php

require_once __DIR__ . '/Db.connector.php';
require_once __DIR__ . '/../controllers/auth.controller.php';
require_once __DIR__ . '/../controllers/monsters.controller.php';

class DatabaseSeeder {
    private $pdo;
    private $authController;
    private $monsterController;

    public function __construct() {
        $db = new DbConnector();
        $this->pdo = $db->pdo;
        $this->authController = new AuthController();
        $this->monsterController = new MonsterController();
    }

    public function seed(): void {
        // Créer un utilisateur de test
        $this->authController->register(
            "testuser",
            "test@example.com",
            "password123"
        );

        // Créer quelques monstres de base
        $monsters = [
            ["Dragon", "Dragon", 3],
            ["Hydre", "Serpent", 5],
            ["Griffon", "Oiseau", 1],
            ["Cerbère", "Chien", 3],
            ["Chimère", "Lion", 3]
        ];

        foreach ($monsters as $monster) {
            $this->monsterController->createMonster(
                $monster[0], // name
                $monster[1], // type
                $monster[2], // heads
                1           // user_id (1 = premier utilisateur créé)
            );
        }

        echo "Base de données peuplée avec succès !\n";
        echo "Utilisateur de test créé :\n";
        echo "Email: test@example.com\n";
        echo "Mot de passe: password123\n";
    }
}