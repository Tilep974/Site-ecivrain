<?php

namespace Livre\Domain;

class Article
{
	private $id;
	
	private $title;
	
	private $content;
	
	private $date;
	
	private $last_modif;
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}
	
    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
        return $this;
    }
	
	public function getLastModif() {
        return $this->last_modif;
    }

    public function setLastModif($last_modif) {
        $this->last_modif = $last_modif;
        return $this;
    }
}