<?php

namespace Livre\Domain;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
	
	private $id;
	
	private $username;
	
	private $password;
	
	private $salt;
	
	private $role;
	
	public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
        return $this;
    }

    public function getRoles()
    {
        return array($this->getRole());
    }

    public function eraseCredentials() {
    }
}