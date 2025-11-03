<?php

class Type {

    private ?int $id = null;
    private string $name;

    /**
     * @param string $name
     * Constructeur d'un type de monstre, ce type possède un nom
     */
    public function __construct(string $name){
        $this->setName($name);
    }


     /**
    * @return int $id
    * Retourne l'id du type
    */
    public function getId() : int{
        return $this->id;
    }

    /**
    * @return self
    * Attribut un ID unique au type
    */
    public function setId(int $id): self{
        $this->id = $id;
        return $this;
    }

    /**
     * @return string $name
     * Retourne le nom du type de monstre
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     * Attribue un nom à un type de monstre
     */
    public function setName(string $name) : self{
        $this->name = $name;

        return $this;
    }

}