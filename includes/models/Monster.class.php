<?php
    class Monster{
        
        private ?int $id = null;
        private string $name;
        private int $heads;
        private string $description;
        private string $img;
        private int $attack_score;
        private int $defense_score;
        private int $health_score;

        /**
         * @param string $name
         * @param int $heads
         * @param int $attack_score
         * @param int $defense_score
         * @param int $health_score
         * @param string $description
         * @param string $img
         * Constructeur type d'un monstre qui posséde un nom, un nombre de têtes, un score d'attaque, de défense
         * et de vie ainsi qu'une description et une image (les stats, la description et l'image sont
         * générés avec Pollinations.ai avec des fichiers prompts dans le dossier pollinations et des méthodes
         * dans le monster controller)
         */
        public function __construct(string $name, int $heads, int $attack_score, int $defense_score, int $health_score,
         string $description, string $img)
        {
            $this->setName($name);
            $this->setHeads($heads);
            $this->setDescription($description);
            $this->setImg($img);
            $this->setAttackScore($attack_score);
            $this->setDefenseScore($defense_score);
            $this->setHealthScore($health_score);
        }

        /**
         * @return int $id
         * Retourne l'id du monstre
         */
        public function getId() : int{
            return $this->id;
        }

        /**
         * @return self
         * Attribut un ID unique au monstre
         */
        public function setId(int $id): self{
            $this->id = $id;
            return $this;
        }

        /**
         * @return string $name
         * Retourne le nom du monstre
         */
        public function getName() : string {
            return $this->name;
        }

        /**
         * @return self
         * Attribue un nom au monstre 
         */
        public function setName($name) : self {
            $this->name = $name;

            return $this;
        }

        /**
         * @return int $heads
         * Retourne le nombre de têtes du monstre
         */
        public function getHeads() : int {
            return $this->heads;
        }

        /**
         * @return self
         * Attribue un nombre de têtes au monstre
         */
        public function setHeads($heads) : self {
            $this->heads = $heads;

            return $this;
        }

        /**
         * @return string $description
         * Retourne la description du monstre
         */
        public function getDescription(): string {
            return $this->description;
        }

        /**
         * @return self
         * Attribue une description au monstre
         */
        public function setDescription($description) : self {
            $this->description = $description;

            return $this;
        }

        /**
         * @return string $img
         * Retourne l'image du monstre
         */
        public function getImg(): string {
            return $this->img;
        }

        /**
         * @return self
         * Attribute une image au monstre
         */
        public function setImg($img) : self {
            $this->img = $img;

            return $this;
        }

        /**
         * @return int $attack_score
         * Retourne le score d'attaque du monstre
         */
        public function getAttackScore() : int {
            return $this->attack_score;
        }


        /**
         * @return self
         * Attribute un score d'attaque au monstre
         */
        public function setAttackScore($attack_score) : self {
            $this->attack_score = $attack_score;

            return $this;
        }

        /**
         * @return int $defense_score
         * Retourne le score de défense du monstre
         */
        public function getDefenseScore() : int {
            return $this->defense_score;
        }


        /**
         * @return self
         * Attribute un score de défense au monstre
         */
        public function setDefenseScore($defense_score) : self {
            $this->defense_score = $defense_score;

            return $this;
        }

        /**
         * @return int $health_score
         * Retourne le nombre de points de vie du monstre
         */
        public function getHealthScore() : int {
            return $this->health_score;
        }


        /**
         * @return self
         * Attribute un score de points de vie au monstre
         */
        public function setHealthScore($health_score) : self {
            $this->health_score = $health_score;

            return $this;
        }


    }