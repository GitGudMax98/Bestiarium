<?php

require_once __DIR__ . '/../models/Monster.class.php';
require_once __DIR__ . '/../db/Db.connector.php';

class MonsterController {

    private $pdo;

        /**
     * Initialise la connexion à la base de données avec dBConnector
     */
        public function __construct()
    {
        $db = new DbConnector();
        $this->pdo = $db->pdo;
    }

    /**
     * @param string $name
     * @param int $heads
     * @param int $user_id
     * @return string JSON contenant le message de succès et les infos du monstre créé
     * 
     * Méthode de création d'un nouveau monstre qui posséde un nom, un nombre de têtes, ainsi que l'ID de
     * l'utilisateur qui l'a créé (on récupére l'ID de l'utilisateur connecté par son token). 
     * Renvoie un Json avec message de succès et les infos du nouveau monstre
     */
    public function createMonster(string $name, int $heads, int $user_id){

        /** Création d'une nouvelle instance d'un monstre */
        $monster = new Monster($name, $heads);

        // Requête SQL
        $request = "
            INSERT INTO monsters (name, heads, user_id)
            VALUES (:name, :heads, :user_id)
        ";

        // Prépare et exécute la requête SQL avec les valeurs du monstre créé
        $stmt = $this->pdo->prepare($request);
        $stmt->execute([
            'name' => $monster->getName(),
            'heads' => $monster->getHeads(),
            'user_id' => $user_id
        ]);

        // Récupére l'ID auto-incrémenté
        $monster->setId($this->pdo->lastInsertId());

        // Stock le résultat
        $result = [
            'message' => 'Monstre créé avec succès',
            'monster' => [
                'id' => $monster->getId(),
                'name' => $monster->getName(),
                'heads' => $monster->getHeads(),
                'user_id' => $user_id
            ]
        ];

        // Retourne le résultat sous forme de JSON
        return json_encode($result, JSON_PRETTY_PRINT);

    }

    /**
     * @param int $user_id
     * 
     * Récupére tous les monstres d'un utilisateur connecté
     */
    public function getAllMonsters(int $user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM monsters WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $monsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode([
            'user_id' => $user_id,
            'monsters' => $monsters
        ], JSON_PRETTY_PRINT);
    }

    /**
     * @param int $monster_id
     * @param int $user_id
     * 
     * Récupére un monstre spécifique par son ID d'un utilisateur connecté
     */
    public function getMonsterByID(int $monster_id, int $user_id){
        $stmt = $this->pdo->prepare("
            SELECT * FROM monsters 
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([
            'id' => $monster_id,
            'user_id' => $user_id
        ]);
        $monster = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$monster) {
            http_response_code(404);
            return json_encode(['error' => 'Monstre introuvable']);
        }

        return json_encode($monster, JSON_PRETTY_PRINT);
    }

}