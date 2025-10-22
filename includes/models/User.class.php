<?php
/**
 * Modèle d'un utilisateur
 */
class User{

    private ?int $id = null;
    private string $username;
    private string $email;
    private string $password;


    /**
     * @param string $username
     * @param string $email
     * @param string $password
     * Constructeur type d'un utilisateur qui posséde un pseudo, un email et un mot de passe
     */
    public function __construct(string $username,string $email,string $password){
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setPassword($password);
    }

    /**
     * @return int $id
     * Retourne l'id de l'utilisateur
     */
    public function getId() : int{
        return $this->id;
    }

    /**
     * @return self
     * Attribut un ID unique à l'utilisateur
     */
    public function setId(int $id): self{
        $this->id = $id;
        return $this;
    }

    /**
     * @return string $username
     * Retourne le pseudo de l'utilisateur
     */
    public function getUsername() : string{
        return $this->username;
    }

    /**
     * @param string $username
     * @return self
     * Attribue un pseudo à l'utilisateur
     */
    public function setUsername($username) : self{
        $this->username = $username;

        return $this;
    }

    /**
     * @return string $email
     * Retourne l'email de l'utilisateur
     */
    public function getEmail() : string{
        return $this->email;
    }

    /**
     * @param string $email
     * @return self
     * Attribue un email à l'utilisateur
     */
    public function setEmail($email) : self{
        $this->email = $email;

        return $this;
    }

    /**
     * @return string $password
     * Retourne le mot de passe de l'utilisateur
     */
    public function getPassword() : string {
        return $this->password;
    }

    /**
     * @param string $password
     * @return self
     * Attribue un mot de passe à l'utilisateur
     */
    public function setPassword($password) : self{
        //  On hash le mot de passe ici
        $this->password = password_hash($password, PASSWORD_DEFAULT);

        return $this;
    }


}