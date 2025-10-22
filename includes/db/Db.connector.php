<?php

/**
 * Gère la connexion à la bdd sqlite
 */
class DbConnector{

    public PDO $pdo;

    public function __construct(){

        /**
         * Chemin vers le fichier db.sqlite
         */
        $bdd = "sqlite:" . __DIR__ . "/db.sqlite";

        /**
         * Connexion à la bdd
         */
        try {
            $this->pdo = new PDO($bdd);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
}


