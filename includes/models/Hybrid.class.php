<?php

require_once __DIR__ . '/Monster.class.php';

class Hybrid extends Monster{

    private int $parent1_id;
    private int $parent2_id;

    /**
     * // Reprend les paramètres du constructeur parent Monster ainsi que :
     * @param int $parent1_id
     * @param int $parent2_id
     */
    public function __construct(int $parent1_id, int $parent2_id, string $name, int $type_id, int $heads, int $attack_score, int $defense_score, int $health_score, string $description, string $img) {
        $this->setParent1Id($parent1_id);
        $this->setParent2Id($parent2_id);
        parent::__construct(
            $name, 
            $type_id, 
            $heads, 
            $attack_score, 
            $defense_score, 
            $health_score,
            $description, 
            $img
        );
    }

    /**
     * @return int $parent1_id
     * Récupére l'id du premier parent
     */
    public function getParent1Id() : int {
        return $this->parent1_id;
    }

    /**
     * @param int $parent1_id
     * @return self
     * Attribue l'id de son parent1 au monstre hybride
     */
    public function setParent1Id($parent1_id) : self {
        $this->parent1_id = $parent1_id;

        return $this;
    }

    /**
     * @return int $parent2_id
     * Récupére l'id du deuxième parent
     */
    public function getParent2Id() : int {
        return $this->parent2_id;
    }

    /**
     * @param int $parent2_id
     * @return self
     * Attribue l'id de son parent2 au monstre hybride
     */
    public function setParent2Id($parent2_id) : self {
        $this->parent2_id = $parent2_id;

        return $this;
    }
    
}