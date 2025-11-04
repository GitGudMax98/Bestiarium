<?php

require_once __DIR__ . '/../models/Monster.class.php';
require_once __DIR__ . '/../db/Db.connector.php';
require_once __DIR__ . '/../controllers/types.controller.php';

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
     * Lecture et rendu d'un prompt à partir d'un fichier 
     * 
     * @param string $filePath Le chemin vers le fichier monster.description.prompt ou monster.image.prompt
     * @param array $vars les variables {} qui permettent de générer un prompt et donc une description dynamique,
     * en l'occurence name et heads
     * 
     * @return string $prompt le prompt dynamique qu'on utilise ensuite dans les méthodes generateDescription
     * et generateImg
     */
    private function renderPrompt(string $filePath, array $vars): string {
        if (!file_exists($filePath)) {
            throw new Exception("Prompt file not found: " . $filePath);
        }

        $prompt = file_get_contents($filePath);
        foreach ($vars as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }
        return trim($prompt);
    }

    /**
     * @param string $fileName le nom du fichier de prompt
     * @param array $vars variables du prompt pour que ce dernier soit customizé
     * @return string retourne le prompt avec la methode renderPrompt
     */
    public function customPrompt($fileName, array $vars) : string{
        $filePath = __DIR__ . '/../pollinations/' . $fileName;
        return $this->renderPrompt($filePath, $vars);
    }

    /**
     * Génère et renvoie un JSON qui contient une description, les statistiques d'attaque, 
     * de défense et de vie du monstre via Pollinations.AI
     * 
     * @param string $name Le nom du monstre
     * @param string $type Le type du monstre
     * @param int $heads Le nombre de têtes du monstre
     * @return JSON
     */
    private function generateDescription(string $name, string $type, int $heads): array {
        
        $prompt = $this->customPrompt('monster.description.prompt', [
            'name' => $name,
            'type' => $type,
            'heads' => $heads
        ]);

        // 🧩 On ajoute au prompt l'instruction claire pour Pollinations :
        $prompt .= "\n\nDonne la réponse UNIQUEMENT en JSON au format suivant :
        {
        \"description\": \"...description immersive et détaillée du monstre...\",
        \"attack_score\": nombre_entre_1_et_100,
        \"defense_score\": nombre_entre_1_et_100,
        \"health_score\": nombre_entre_50_et_500
        }";

        $url = "https://text.pollinations.ai/";
        $data = ["messages" => [["role" => "user", "content" => $prompt]]];

        $options = [
            "http" => [
                "header"  => "Content-type: application/json\r\n",
                "method"  => "POST",
                "content" => json_encode($data),
                "timeout" => 30
            ]
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if (!$result) {
            return [
                "description" => "Une créature mystérieuse dont on ignore tout...",
                "attack_score" => rand(10, 50),
                "defense_score" => rand(10, 50),
                "health_score" => rand(100, 300)
            ];
        }

        // 🧠 Tentative de décodage JSON
        $decoded = json_decode($result, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['description'])) {
            return $decoded;
        }

        // Si Pollinations ne renvoie pas du JSON valide
        return [
            "description" => trim(strip_tags($result)),
            "attack_score" => rand(10, 100),
            "defense_score" => rand(10, 100),
            "health_score" => rand(50, 500)
        ];
    }

    /**
     * Génère une image via Pollinations.AI
     * 
     * @param string $name Le nom du monstre
     * @param string $type Le type du monstrde
     * @param int $heads Le nombre de têtes du monstre
     * @return string l'url de l'image (cette dernière est stockée dans le dossier images du projet)
     */
    private function generateImage(string $name, string $type, int $heads): string {
        
        $prompt = $this->customPrompt('monster.image.prompt', [
            'name' => $name,
            'type' => $type,
            'heads' => $heads
        ]);

        $url = "https://image.pollinations.ai/prompt/" . urlencode($prompt);
        $imageData = @file_get_contents($url);

        if (!$imageData) {
            return "default_monster.png";
        }

        $imageDir = __DIR__ . '/../../images/';
        if (!file_exists($imageDir)) mkdir($imageDir, 0777, true);

        $filename = 'monster_' . time() . '.png';
        file_put_contents($imageDir . $filename, $imageData);

        return $filename;
    }

    /**
     * @param string $name
     * @param string $type
     * @param int $heads
     * @return string JSON contenant le message de succès et les infos du monstre créé
     * 
     * Méthode de création d'un nouveau monstre, l'utilisateur rentre un nom, un type,
     * et un nombre de tête et on récupére l'ID de l'utilisateur qui l'a créé par son token
     * 
     * Renvoie un Json avec message de succès et les infos du nouveau monstre
     */
    public function createMonster(string $name, string $type, int $heads, int $user_id){

        // Récupére ou créé le type et récupére son ID
        $typesController = new TypesController();
        $typeObj = $typesController->getOrCreateType($type);
        $type_id = $typeObj->getId();
        $typeName = $typeObj->getName();

        // Génére description et stats
        $generation = $this->generateDescription($name, $typeName, $heads);
        
        $description = $generation['description'];
        $attack_score = $generation['attack_score'];
        $defense_score = $generation['defense_score'];
        $health_score = $generation['health_score'];

        // Génére une image
        $img = $this->generateImage($name, $typeName, $heads);

        /** Création d'une nouvelle instance d'un monstre */
        $monster = new Monster(
            $name, 
            $type_id, 
            $heads,
            $attack_score, 
            $defense_score, 
            $health_score, 
            $description, 
            $img
        );

        // Requête SQL
        $request = "
            INSERT INTO monsters (name, type_id, heads, attack_score, defense_score, health_score, description, img, user_id)
            VALUES (:name, :type_id, :heads, :attack_score, :defense_score, :health_score, :description, :img, :user_id)
        ";

        // Prépare et exécute la requête SQL avec les valeurs du monstre créé
        $stmt = $this->pdo->prepare($request);
        $stmt->execute([
            'name' => $monster->getName(),
            'type_id' => $monster->getTypeId(),
            'heads' => $monster->getHeads(),
            'description' => $monster->getDescription(),
            'attack_score' => $monster->getAttackScore(),
            'defense_score' => $monster->getDefenseScore(),
            'health_score' => $monster->getHealthScore(),
            'img' => $monster->getImg(),
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
                'type_id' => $monster->getTypeId(),
                'heads' => $monster->getHeads(),
                'attack_score' => $monster->getAttackScore(),
                'defense_score' => $monster->getDefenseScore(),
                'health_score' => $monster->getHealthScore(),
                'description' => $monster->getDescription(),
                'img' => $monster->getImg(),
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