<?php
    class Battle {

        private ?int $id = null;
        private int $monster1_id;
        private int $monster2_id;
        private string $result;

        /**
         * @param int $monster1_id
         * @param int $monster2_id
         * 
         * Constructeur type d'un combat de monstre, prend en paramètre les ID des deux monstres qui vont
         * combattre à la mort
         */
        public function __construct(int $monster1_id, int $monster2_id)
        {
            $this->setMonster1_id($monster1_id);
            $this->setMonster2_id($monster2_id);
        }

        /**
        * @return int $id
        * Retourne l'id de la battle
        */
        public function getId() : int{
            return $this->id;
        }

        /**
        * @return self
        * Attribut un ID unique à la battle
        */
        public function setId(int $id): self{
            $this->id = $id;
            return $this;
        }

        /**
         * @return $this->monster1_id
         * 
         * Récupère la valeur de l'id du monstre 1
         */
        public function getMonster1_id() : int{
            return $this->monster1_id;
        }

        /**
         * @param int $monster1_id
         * @return self
         * 
         * Attribue un ID au monstre 1
         */
        public function setMonster1_id(int $monster1_id) : self{
            $this->monster1_id = $monster1_id;

            return $this;
        }

        /**
         * @return $this->monster2_id
         * 
         * Récupère la valeur de l'id du monstre 2
         */
        public function getMonster2_id() : int {
            return $this->monster2_id;
        }

        /**
         * @param int $monster2_id
         * @return self
         * 
         * Attribue un ID au monstre 2
         */
        public function setMonster2_id(int $monster2_id) : self{
            $this->monster2_id = $monster2_id;

            return $this;
        }

        /**
         * @return string
         * 
         * Récupère la valeur du résultat de la battle
         */
        public function getResult() : string{
            return $this->result;
        }

        /**
         * @param string @result
         * @return self
         * 
         * Attribue une valeur au résultat de la battle
         */
        public function setResult(string $result) : self{
            $this->result = $result;

            return $this;
        }

    }



