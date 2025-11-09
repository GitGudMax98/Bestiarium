<?php

require_once __DIR__ . '/../models/Battle.class.php';
require_once __DIR__ . '/../db/Db.connector.php';
require_once __DIR__ . '/../controllers/monsters.controller.php';

class BattleController {

    private $pdo;
    private $monsterController;

    public function __construct() {
        $db = new DbConnector();
        $this->pdo = $db->pdo;
        $this->monsterController = new MonsterController();
    }

    public function createBattle(int $monster1_id, int $monster2_id, int $user_id): string {

        // 🔹 Récupération des infos des deux monstres
        $stmt = $this->pdo->prepare("
            SELECT * FROM monsters 
            WHERE id IN (?, ?) 
            AND user_id = ?
        ");

        $stmt->execute([$monster1_id, $monster2_id, $user_id]);
        $monsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($monsters) < 2) {
            http_response_code(404);
            return json_encode(['error' => 'Un ou les deux monstres sont introuvables']);
        }

        $monstersById = [];
        foreach ($monsters as $mon) {
            $monstersById[$mon['id']] = $mon;
        }

        $m1 = $monstersById[$monster1_id] ?? null;
        $m2 = $monstersById[$monster2_id] ?? null;

        if (!$m1 || !$m2) {
            http_response_code(404);
            return json_encode(['error' => 'Un ou les deux monstres sont introuvables']);
        }

        // 🔹 Génération du résultat via Pollinations
        $result_json = $this->generateBattleResults($m1, $m2);
        $result = json_decode($result_json, true);

        // 🔹 Identifier le vainqueur et le perdant
        $vainqueur = $result['vainqueur'] ?? null;

        if (!$vainqueur) {
            // Combat indécis : on ne supprime personne
            $perdant = null;
        } else {
            $perdant = ($m1['name'] === $vainqueur) ? $m2 : $m1;

            // 🔹 Supprimer le perdant
            $deleteStmt = $this->pdo->prepare("DELETE FROM monsters WHERE id = :id");
            $deleteStmt->execute(['id' => $perdant['id']]);
        }   

        // 🔹 Création d'une instance Battle
        $battle = new Battle($monster1_id, $monster2_id);
        $battle->setResult($vainqueur);

        // 🔹 Sauvegarde du combat
        $stmt = $this->pdo->prepare("
            INSERT INTO battles (result, monster1_id, monster2_id)
            VALUES (:result, :monster1_id, :monster2_id)
        ");
        $stmt->execute([
            'result' => $battle->getResult(),
            'monster1_id' => $battle->getMonster1_id(),
            'monster2_id' => $battle->getMonster2_id()
        ]);
        $battle->setId($this->pdo->lastInsertId());

        // 🔹 Retourne la réponse
        return json_encode([
            'message' => 'Combat créé avec succès',
            'battle' => [
                'id' => $battle->getId(),
                'monster1' => $m1['name'],
                'monster2' => $m2['name'],
                'vainqueur' => $vainqueur,
                'perdant_supprime' => $perdant['name'] ?? null
            ]
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Génère la narration du combat via Pollinations
     */
    private function generateBattleResults(array $m1, array $m2): string {
        // 🔹 Utilisation du prompt battle.prompt avec la méthode du MonsterController
        $prompt = $this->monsterController->customPrompt('battle.prompt', [
            'monster1_name' => $m1['name'],
            'monster1_type' => $m1['type_id'],
            'monster1_heads' => $m1['heads'],
            'monster1_attack' => $m1['attack_score'],
            'monster1_defense' => $m1['defense_score'],
            'monster1_health' => $m1['health_score'],
            'monster2_name' => $m2['name'],
            'monster2_type' => $m2['type_id'],
            'monster2_heads' => $m2['heads'],
            'monster2_attack' => $m2['attack_score'],
            'monster2_defense' => $m2['defense_score'],
            'monster2_health' => $m2['health_score']
        ]);

        // 🔹 Envoi à Pollinations
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

        // 🔹 Si Pollinations échoue
        if (!$result) {
            return "Le combat entre {$m1['name']} et {$m2['name']} fut si brutal que nul ne sut qui gagna.";
        }

        return trim($result);
    }
}
