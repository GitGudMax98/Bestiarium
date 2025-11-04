<?php

// Appel des différents controller
require_once __DIR__ . '/includes/controllers/auth.controller.php';
require_once __DIR__ . '/includes/controllers/monsters.controller.php';

header('Content-Type: application/json');

// Récupère l'URL après localhost:8000/
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = ltrim($uri, '/');

$method = $_SERVER['REQUEST_METHOD'];

// Routes (check l'url pour adapter selon la route)
switch ($uri){

    // Routes auth (register, login, logout)

        // Route pour créer un utilisateur
        case 'register': 
            if ($method === 'POST') {

                // Récupére les données envoyées dans le body (les infos du nouvel utilisateur)
                $data = json_decode(file_get_contents('php://input'), true);

                // Vérifie que les infos nécessaires sont bien présentes
                if (isset($data['username'], $data['email'], $data['password'])) {

                    // Si oui enregistre le nouvel utilisateur
                    $auth = new AuthController();
                    $response = $auth->register($data['username'], $data['email'], $data['password']);
                    echo $response;

                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Données manquantes : username, email et password requis']);
                }

            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée, utilisez POST']);
            }
            break;
        
        // Route pour se connecter
        case 'login':
            if($method === 'POST'){
                $data = json_decode(file_get_contents('php://input'), true);

                if (isset($data['email'], $data['password'])){

                    $auth = new AuthController();
                    $response = $auth->login($data['email'], $data['password']);
                    echo $response;
                    
                }   else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Données incorrectes']);     
                }
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée, utilisez POST']);
            }
            break;

        // Route pour se déconnecter (à faire)
        case 'logout':
            if ($method === 'POST') {
                $auth = new AuthController();
                $response = $auth->logout();
                echo $response;
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée, utilisez POST']);
            }
            break;
    
    // Routes monsters

        // Route pour créer un monstre
        case 'monster/create': 
            if ($method === 'POST') {

                $auth = new AuthController();

                try {
                    // Vérifie le token et récupère l'id utilisateur
                    $user_id = $auth->verifyToken();
                } catch (Exception $e) {
                    http_response_code(401);
                    echo json_encode(['error' => $e->getMessage()]);
                    exit;
                }

                // Récupère les données envoyées dans le body
                $data = json_decode(file_get_contents('php://input'), true);

                // Vérifie que les infos nécessaires sont bien présentes
                if (isset($data['name'], $data['heads'])) {

                    // Si oui crée le monstre avec le contrôleur
                    $monsterController = new MonsterController();
                    $response = $monsterController->createMonster(
                        $data['name'],
                        $data['type'],
                        (int)$data['heads'],
                        (int)$user_id
                    );

                    // Renvoie la réponse JSON
                    echo $response;        
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Données manquantes : name et heads requis']);
                }

            }else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée, utilisez POST']);
            }
            break;

        // Route pour récupérer tous les monstres d'un utilisateur connecté
        case 'monster/all' :
            if ($method === 'GET') {
                $auth = new AuthController();
                try {
                    $user_id = $auth->verifyToken();
                } catch (Exception $e) {
                    http_response_code(401);
                    echo json_encode(['error' => $e->getMessage()]);
                    exit;
                }

                $monsterController = new MonsterController();
                echo $monsterController->getAllMonsters($user_id);
                }
            break;  

        // Route pour récupérer un monstre spécifique par son ID d'un utilisateur connecté
        case (str_starts_with($uri, 'monster/show/')): 
            if ($method === 'GET') {
                $parts = explode('/', $uri); // découpe l'URL en segments dans un array
                $monster_id = (int)$parts[2]; // ID = 3 ème élément de l'array

                $auth = new AuthController();
                try {
                    $user_id = $auth->verifyToken();
                } catch (Exception $e) {
                    http_response_code(401);
                    echo json_encode(['error' => $e->getMessage()]);
                    exit;
                }

                $monsterController = new MonsterController();
                echo $monsterController->getMonsterById($monster_id, $user_id);
            }
            break;
            
        default: // Comportement par défault si la route n'est pas trouvée
            http_response_code(404);
            echo json_encode(['error' => 'Route non trouvée']);
            break;

}