<?php
namespace Livre\Domain;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class Flag {
	
    private $id;

    private $date;

    private $ip;

    private $user;

    private $comment;

    private $article;


    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment(Comment $comment) {
        $this->comment = $comment;
        return $this;
    }

    public function getArticle() {
        return $this->article;
    }

    public function setArticle(Article $article) {
        $this->article = $article;
        return $this;
    }

    public function getIp() {
        return $this->ip;
    }

    public function setIp($ip) {
        $this->ip = $ip;
        return $this;
    }




    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        //ID
        $metadata->addPropertyConstraint('id', new Assert\Type(array(
            'type' => 'integer',
            'message' => 'L\'ID associé au report doit être de type Integer ou null.'
        )));



        //User
        $metadata->addPropertyConstraint('user', new Assert\NotBlank(array(
            'message' => 'L\'auteur du report doit être renseigné.'
        )));
        $metadata->addPropertyConstraint('user', new Assert\Type(array(
            'type' => 'object',
            'message' => 'L\'utiliateur associé doit être de type Object.'
        )));



        //Comment
        $metadata->addPropertyConstraint('comment', new Assert\NotBlank(array(
            'message' => 'L\'auteur du report doit être renseigné.'
        )));
        $metadata->addPropertyConstraint('comment', new Assert\Type(array(
            'type' => 'object',
            'message' => 'Le commentaire associé doit être de type Object.'
        )));



        //Article
        $metadata->addPropertyConstraint('article', new Assert\NotBlank(array(
            'message' => 'L\'article du commentaire associé au report doit être renseigné.'
        )));
        $metadata->addPropertyConstraint('article', new Assert\Type(array(
            'type' => 'object',
            'message' => 'L\'article associé doit être de type Object.'
        )));



        //Date
        $metadata->addPropertyConstraint('date', new Assert\DateTime(array(
            'message' => 'La date du report doit être un objet de type DateTime, ou une string valide au format YYYY-MM-DD hh:ii:ss.'
        )));



        //IP
        $metadata->addPropertyConstraint('ip', new Assert\Ip(array(
            'version' => 'all',
            'message' => 'L\'adresse IP associée au report doit être au format valide IPv4 ou IPv6.'
        )));
    }
}
