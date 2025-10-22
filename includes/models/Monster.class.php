<?php
class Monster{
    
    private ?int $id = null;
    private int $user_id;
    private string $name;
    private int $heads;

    /**
     * @param string $name
     * @param int $heads
     * Constructeur type d'un monstre qui posséde un nom et un nombre de têtes
     */
    public function __construct(string $name, int $heads)
    {
        $this->setName($name);
        $this->setHeads($heads);
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

}