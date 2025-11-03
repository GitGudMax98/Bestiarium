<?php

require_once __DIR__ . '/../models/Type.class.php';
require_once __DIR__ . '/../db/Db.connector.php';

class TypesController {

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
     * Cherche un type par son nom et le renvoie si trouvé.
     * Sinon, crée le type et le renvoie.
     * @param string $name
     * @return Type instance de la classe type
     */
    public function getOrCreateType(string $name): Type {
        // Cherche le type
        $stmt = $this->pdo->prepare("SELECT id, name FROM types WHERE name = :name");
        $stmt->execute(['name' => $name]);

        if ($row = $stmt->fetch()) {
            $type = new Type($row['name']);
            $type->setId($row['id']);
            return $type;
        }

        // Sinon, créer le type
        $stmt = $this->pdo->prepare("INSERT INTO types (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);

        $type = new Type($name);
        $type->setId($this->pdo->lastInsertId());

        return $type;
    }
    
}