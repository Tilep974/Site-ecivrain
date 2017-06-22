<?php

namespace Livre\Domain;

class Comment 
{
    private $id;

    private $author;

    private $content;

    private $article;
	
	private $parent_id;
	
	private $level;
	
	private $date;
	
	private $is_deleted;
	
	private $number_flags;
	
	

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function getArticle() {
        return $this->article;
    }

    public function setArticle(Article $article) {
        $this->article = $article;
        return $this;
    }
	
	public function getParentId() {
		return $this->parent_id;
	}
	
	public function setParentId($parent_id) {
		$this->parent_id = $parent_id;
		return $this;
	}
	
	public function getLevel() {
		return $this->level;
	}
	
	public function setLevel($level) {
		$this->level = $level;
		return $this;
	}
	
	    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
        return $this;
    }
	
	public function getIsDeleted() {
        return $this->is_deleted;
    }

    public function setIsDeleted($is_deleted) {
        $this->is_deleted = $is_deleted;
        return $this;
    }
	
	public function getNumberFlags() {
        return $this->number_flags;
    }

    public function setNumberFlags($number_flags) {
        $this->number_flags = $number_flags;
        return $this;
    }
	
}