<?php

namespace Livre\DAO;

use Livre\Domain\Comment;

class CommentDAO extends DAO
{
	private $articleDAO;
	
	private $userDAO;
	
	public function setArticleDAO(ArticleDAO $articleDAO) {
		$this->articleDAO = $articleDAO;
	}
	
	public function setUserDAO(UserDAO $userDAO) {
		$this->userDAO = $userDAO;
	}
	
	public function findAllByArticle($articleId) {
		$article = $this->articleDAO->find($articleId);
		$sql = "select com_id, com_content, usr_id, com_level, parent_id from t_comment where art_id=? order by com_id";
		$result = $this->getDb()->fetchAll($sql, array($articleId));
		
		$comments = array();
		foreach ($result as $row) {
			$comId = $row['com_id'];
			$comment = $this->buildDomainObject($row);
			$comment->setArticle($article);
			$comments[$comId] = $comment;
		}
		return $comments;
	}
	
	public function save(Comment $comment) {
		$commentData = array(
			'art_id' => $comment->getArticle()->getId(),
			'usr_id' => $comment->getAuthor()->getId(),
			'com_content' => $comment->getContent(),
			'com_level' => $comment->getLevel(),
			'parent_id' => $comment->getParentId(),
		);
		
		if ($comment->getId()) {
			$this->getDb()->update('t_comment', $commentData, array('com_id' => $comment->getId()));
		} else {
			$this->getDb()->insert('t_comment', $commentData);
			$id = $this->getDb()->lastInsertId();
			$comment->setId($id);
		}
	}
	
    public function findAll() {
        $sql = "select * from t_comment order by com_id DESC";
        $result = $this->getDb()->fetchAll($sql);

        //Convert query results to an array of Domain objects
        $comments = array();
        foreach ($result as $row) {
            $commentId = $row['com_id'];
            $comments[$commentId] = $this->buildDomainObject($row);
        }

        return $comments;
    }
	
	public function findAllParentsByArticle($articleId) {
		$article = $this->articleDAO->find($articleId);
		$sql = "SELECT * from t_comment where art_id=? and parent_id is null";
		$result = $this->getDb()->fetchAll($sql, array($articleId));
		
		$comments = array();
		foreach($result as $row) {
			$commentId = $row['com_id'];
			$comment = $this->buildDomainObject($row);
			
			$comment->setArticle($article);
		
			$comments[$commentId] = $comment;
		}
		return $comments;
	}
	
	public function findAllChildrenByArticle($articleId) {
		$article = $this->articleDAO->find($articleId);
		
		$sql = "select * from t_comment where art_id=? and parent_id is not null";
		$result = $this->getDB()->fetchAll($sql, array($articleId));
		
		$comments = array();
		foreach ($result as $row) {
			$commentId = $row['com_id'];
			$comment = $this->buildDomainObject($row);
			
			$comment->setARticle($article);
			
			$comments[$commentId] = $comment;
		}
		return $comments;
	}
	
	public function deleteAllByArticle($articleId) {
		$this->getDb()->delete('t_comment', array('art_id' => $articleId));
	}
	
	public function find($id) {
		$sql = "select * from t_comment where com_id=?";
		$row = $this->getDb()->fetchAssoc($sql, array($id));
		
		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new \Exception("No Comment matching id " . $id);
	}
	
	public function delete($id) {
		$this->getDb()->delete('t_comment', array('com_id' => $id));
	}
	
	public function deleteAllByUser($userId) {
		$this->getDb()->delete('t_comment', array('usr_id' => $userId));
	}	
	
    protected function buildDomainObject(array $row) {
        $comment = new Comment();

        $comment->setId((int) $row['com_id']);
        $comment->setContent($row['com_content']);
        $comment->setParentId(is_null($row['parent_id']) ? null : (int)$row['parent_id']);
        $comment->setLevel((int) $row['com_level']);


        //Conditional so we build a given Article object only once in findAllByArticle()
        if (array_key_exists('art_id', $row)) {
            //Find and set the associated article
            $articleId = $row['art_id'];
            $article = $this->articleDAO->find($articleId);
            $comment->setArticle($article);
        }
        if (array_key_exists('usr_id', $row)) {
            //Find and set the associated article
            $userId = $row['usr_id'];
            $user = $this->userDAO->find($userId);
            $comment->setAuthor($user);
        }

        return $comment;
    }
}