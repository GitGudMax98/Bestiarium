<?php

require 'vendor/autoload.php';
require_once __DIR__ . '/../models/User.class.php';
require_once __DIR__ . '/../db/Db.connector.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


 class AuthController {

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
     * @param string $username
     * @param string $email
     * @param string $password
     * @return string JSON contenant le message de succès et les infos de l'utilisateur créé
     * 
     * Méthode d'inscription d'un nouvel utilisateur qui s'inscrit avec un email, un pseudo et un mot de passe.
     * Renvoie un Json avec message de succès et les infos du nouvel utilisateur
     */
    public function register(string $username, string $email, string $password){

 
        /** Vérifie que l'email est valide */
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return json_encode(['error' => 'Adresse email invalide']);
        }

        /** Vérifie si l'email est déjà utilisé */
        $checkStmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $checkStmt->execute(['email' => $email]);
        if ($checkStmt->fetch()) {
            http_response_code(409); // Conflit : email déjà existant
            return json_encode(['error' => 'Cet email est déjà utilisé']);
        }


        /** Création d'une   nouvelle instance d'un utilisateur */
        $user = new User($username, $email, $password);

        // Requête SQL
        $request = "
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ";

        // Prépare et exécute la requête SQL avec les valeurs de l'utilisateur créé
        $stmt = $this->pdo->prepare($request);
        $stmt->execute([
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ]);

        // Récupére l'ID auto-incrémenté
        $user->setId($this->pdo->lastInsertId());

        // Stock le résultat
        $result = [
            'message' => 'Utilisateur créé avec succès',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail()
            ]
        ];

        // Retourne le résultat sous forme de JSON
        return json_encode($result, JSON_PRETTY_PRINT);

    }

    /**
     * @param string $email
     * @param string $password
     * @return JSON avec un message de connexion réussie et le token JWT
     * 
     * Méthode de connexion d'un utilisateur, verifie si email et password sont valides puis génére un token
     * JWT
     */
    public function login(string $email, string $password){

        // Cherche l'utilisateur dans la BDD
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie que l'email et le mot de passe donnés par l'utilisateur sont égales à ceux rentrés dans la bdd
        if (!password_verify($password, $user['password']) || $user['email'] !== $email) {
            http_response_code(401);
            return json_encode(['error' => 'Email ou mot de passe incorrect']);
        }

        // Génération du JWT
        $payload = [
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ];
        $secretKey = "TON_SECRET_KEY";
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        // Si la connexion réussie on renvoit un json avec le token JWT
        return json_encode([
            'message' => 'Connexion réussie',
            'token' => $jwt
        ]);

    }

    public function logout(){
        
    }

    
    /**
     * Vérifie le token JWT de l'utilisateur et renvoie l'user_id correspondant
     * (pour les routes réservées à un utilisateur connecté)
     * 
     * @return int $user_id
     * @throws Exception si le token est manquant ou invalide
     */
    function verifyToken(): int {
        $headers = getallheaders();

        // Vérifie la présence du header Authorization
        if (!isset($headers['Authorization'])) {
            throw new Exception('Token manquant dans les headers');
        }

        // Récupère le token (format: Bearer xxx)
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        // Décode le token
        $secretKey = "TON_SECRET_KEY"; // doit être identique à celui de AuthController::login

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            $userEmail = $decoded->email;

            // Récupère l'id utilisateur depuis l'email
            $pdo = (new DbConnector())->pdo;
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $userEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('Utilisateur introuvable');
            }

            return (int)$user['id'];

        } catch (Exception $e) {
            throw new Exception('Token invalide ou expiré : ' . $e->getMessage());
        }
    }

 }