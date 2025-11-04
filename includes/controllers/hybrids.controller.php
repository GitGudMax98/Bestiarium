<?php

require_once __DIR__ . '/Monster.controller.php';
require_once __DIR__ . '/../models/HybridMonster.class.php';
require_once __DIR__ . '/../controllers/types.controller.php';
require_once __DIR__ . '/../db/Db.connector.php';

class HybridController extends MonsterController {

    private $pdo;

    public function __construct() {
        $db = new DbConnector();
        $this->pdo = $db->pdo;
    }

    /**
     * Pollinations : génère description + stats + têtes à partir des 2 parents
     */
    private function generateHybridDescription(array $p1, array $p2): array {
        $prompt = $this->customPrompt('hybrid.description.prompt', [
            'parent1_name' => $p1['name'],
            'parent2_name' => $p2['name'],
            'p1_attack' => $p1['attack_score'],
            'p2_attack' => $p2['attack_score'],
            'p1_defense' => $p1['defense_score'],
            'p2_defense' => $p2['defense_score'],
            'p1_health' => $p1['health_score'],
            'p2_health' => $p2['health_score'],
            'p1_heads' => $p1['heads'],
            'p2_heads' => $p2['heads']
        ]);

        $prompt .= "\n\nRéponds UNIQUEMENT en JSON :
        {
            \"name\": \"...un qui est le mélange de celui des deux parents...\",
            \"description\": \"...description détaillée...\",
            \"attack_score\": nombre_entre_1_et_100,
            \"defense_score\": nombre_entre_1_et_100,
            \"health_score\": nombre_entre_50_et_500,
            \"heads\": nombre_entre_1_et_10
        }";

        $url = "https://text.pollinations.ai/";
        $data = ["messages" => [["role" => "user", "content" => $prompt]]];
        $context = stream_context_create([
            "http" => [
                "header"  => "Content-type: application/json\r\n",
                "method"  => "POST",
                "content" => json_encode($data),
                "timeout" => 30
            ]
        ]);

        $result = @file_get_contents($url, false, $context);
        $decoded = json_decode($result, true);

        if ($decoded && isset($decoded['description'])) {
            return $decoded;
        }

        // fallback en cas d’échec
        return [
            "name" => "Joe",
            "description" => "Un hybride né de {$p1['name']} et {$p2['name']}, fusion imprévisible et sauvage.",
            "attack_score" => rand(30, 100),
            "defense_score" => rand(30, 100),
            "health_score" => rand(100, 400),
            "heads" => rand(1, 5)
        ];
    }

    private function generateHybridImage(string $name, string $p1_name, string $p2_name): string {
        $prompt = $this->customPrompt('hybrid.image.prompt', [
            'name' => $name,
            'parent1' => $p1_name,
            'parent2' => $p2_name
        ]);

        $url = "https://image.pollinations.ai/prompt/" . urlencode($prompt);
        $imageData = @file_get_contents($url);

        if (!$imageData) return "default_monster.png";

        $imageDir = __DIR__ . '/../../images/';
        if (!file_exists($imageDir)) mkdir($imageDir, 0777, true);

        $filename = 'hybrid_' . time() . '.png';
        file_put_contents($imageDir . $filename, $imageData);

        return $filename;
    }

    /**
     * Crée un monstre hybride à partir de deux parents existants
     */
    public function createHybrid(int $parent1_id, int $parent2_id, int $user_id): string {
        // 🔹 Récupération des parents
        $stmt = $this->pdo->prepare("SELECT * FROM monsters WHERE id IN (:p1, :p2)");
        $stmt->execute(['p1' => $parent1_id, 'p2' => $parent2_id]);
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($parents) < 2) {
            http_response_code(404);
            return json_encode(['error' => 'Un ou les deux parents sont introuvables']);
        }

        $p1 = $parents[0];
        $p2 = $parents[1];

        // 🔹 On prend le type du premier parent (simplification)
        $type_id = $p1['type_id'];

        // 🔹 Pollinations s’occupe de tout : description + stats + têtes
        $generation = $this->generateHybridDescription($p1, $p2);

        $name = $generation['name'];
        $description = $generation['description'];
        $attack_score = $generation['attack_score'];
        $defense_score = $generation['defense_score'];
        $health_score = $generation['health_score'];
        $heads = $generation['heads'] ?? rand(1, 5); // fallback léger
        $img = $this->generateHybridImage($name, $p1['name'], $p2['name']);

        // 🔹 Création de l’objet
        $hybrid = new Hybrid($parent1_id, $parent2_id, $name, $type_id, $heads, $attack_score, $defense_score, $health_score, $description, $img);
        $hybrid
            ->setName($name)
            ->setTypeId($type_id)
            ->setHeads($heads)
            ->setAttackScore($attack_score)
            ->setDefenseScore($defense_score)
            ->setHealthScore($health_score)
            ->setDescription($description)
            ->setImg($img);

        // 🔹 Sauvegarde en BDD
        $stmt = $this->pdo->prepare("
            INSERT INTO monsters 
            (name, type_id, heads, attack_score, defense_score, health_score, description, img, user_id, parent1_id, parent2_id, is_hybrid)
            VALUES 
            (:name, :type_id, :heads, :attack_score, :defense_score, :health_score, :description, :img, :user_id, :parent1_id, :parent2_id, 1)
        ");

        $stmt->execute([
            'name' => $hybrid->getName(),
            'type_id' => $hybrid->getTypeId(),
            'heads' => $hybrid->getHeads(),
            'attack_score' => $hybrid->getAttackScore(),
            'defense_score' => $hybrid->getDefenseScore(),
            'health_score' => $hybrid->getHealthScore(),
            'description' => $hybrid->getDescription(),
            'img' => $hybrid->getImg(),
            'user_id' => $user_id,
            'parent1_id' => $parent1_id,
            'parent2_id' => $parent2_id
        ]);

        $hybrid->setId($this->pdo->lastInsertId());

        return json_encode([
            'message' => 'Monstre hybride créé avec succès',
            'hybrid' => [
                'id' => $hybrid->getId(),
                'name' => $hybrid->getName(),
                'parents' => [$p1['name'], $p2['name']],
                'attack_score' => $hybrid->getAttackScore(),
                'defense_score' => $hybrid->getDefenseScore(),
                'health_score' => $hybrid->getHealthScore(),
                'heads' => $hybrid->getHeads(),
                'description' => $hybrid->getDescription(),
                'img' => $hybrid->getImg()
            ]
        ], JSON_PRETTY_PRINT);
    }

}