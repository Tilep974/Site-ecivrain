<?php
namespace Livre\DAO;

use Livre\Domain\Flag;

class FlagDAO extends DAO {
    /**
     * @var \Livre\DAO\ArticleDAO
     */
    private $articleDAO;

    /**
     * @var \Livre\DAO\CommentDAO
     */
    private $commentDAO;

    /**
     * @var \Livre\DAO\UserDAO
     */
    private $userDAO;

    public function setArticleDAO(ArticleDAO $articleDAO) {
        $this->articleDAO = $articleDAO;
    }

    public function setCommentDAO(CommentDAO $commentDAO) {
        $this->commentDAO = $commentDAO;
    }

    public function setUserDAO(UserDAO $userDAO) {
        $this->userDAO = $userDAO;
    }



    /**
     * Returns a flag matching the supplied id.
     *
     * @param integer $id The flag id
     *
     * @return \Livre\Domain\Flag|throws an exception if no matching flag is found
     */
    public function find($id) {
        $sql = "select * from t_flags where flag_id = ?";
        $row = $this->getDb()->fetchAssoc($sql, array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("Aucun flag ne correspond à l'ID : " . $id);
    }

    /**
     * Returns the number of flags for the given comment
     *
     * @param integer $id The comment id
     *
     * @return int The number of flags for the comment.
     */
    public function countByComments() {
        $sql = "select * from t_flags order by flag_com_id";
        $result = $this->getDb()->fetchAll($sql);

        //Convert query results to an array of Domain objects
        $flags = array();
        foreach ($result as $row) {
            $commentId = $row['flag_com_id'];
            $flags[$commentId] = array_key_exists($commentId, $flags) ? $flags[$commentId] + 1 : 1;
        }

        return $flags;
    }

    /**
     * Returns a list of all flags, sorted by date (most recent first).
     *
     * @return array A list of all flags.
     */
    public function findAll() {
        $sql = "select * from t_flags order by flag_date DESC";
        $result = $this->getDb()->fetchAll($sql);

        //Convert query results to an array of Domain objects
        $flags = array();
        foreach ($result as $row) {
            $flagId = $row['flag_id'];
            $flags[$flagId] = $this->buildDomainObject($row);
        }

        return $flags;
    }

    /**
     * Return a list of flags on all comments for a user, sorted by date (most recent first).
     * Array keys being the comment ids for easy use with Twig
     *
     * @param integer $userId The user id.
     *
     * @return array A list of flags on all comments for a user.
     */
    public function findAllByUser($userId) {
        //Find and set the associated article
        $user = $this->userDAO->find($userId);

        //flag_usr_id is not selected by the query
        //so the flag won't be retrieved during buildDomainObject()
        $sql = "select flag_id, flag_date, flag_ip, flag_art_id, flag_com_id from t_flags where flag_usr_id = ? order by flag_date";
        $result = $this->getDb()->fetchAll($sql, array($userId));

        //Convert the query result into an array of domain objects
        $flags = array();
        foreach ($result as $row) {
            $flagComId = $row['flag_com_id'];
            $flag = $this->buildDomainObject($row);

            //The associated article is defined for the current flag in the loop
            $flag->setUser($user);

            $flags[$flagComId] = $flag;
        }

        return $flags;
    }

    /**
     * Creates a Flag object based on a DB row.
     *
     * @param array $row The DB row containing Flag data.
     * @return \Livre\Domain\Flag
     */
    protected function buildDomainObject(array $row) {
        $flag = new Flag();

        $flag->setId((int) $row['flag_id']);
        $flag->setDate($row['flag_date']);
        $flag->setIp(inet_ntop($row['flag_ip']));

        if (array_key_exists('flag_art_id', $row)) {
            //Find and set the associated article
            $articleId = $row['flag_art_id'];
            $article = $this->articleDAO->find($articleId);
            $flag->setArticle($article);
        }
        if (array_key_exists('flag_com_id', $row)) {
            //Find and set the associated comment
            $commentId = $row['flag_com_id'];
            $comment = $this->commentDAO->find($commentId);
            $flag->setComment($comment);
        }
        if (array_key_exists('flag_usr_id', $row)) {
            //Find and set the associated user
            $userId = $row['flag_usr_id'];
            $user = $this->userDAO->find($userId);
            $flag->setUser($user);
        }

        return $flag;
    }

    /**
     * Saves a flag into the database.
     *
     * @param \Livre\Domain\Flag $flag The flag to save
     */
    public function save(Flag $flag) {
        $flagData = array(
            'flag_com_id' => $flag->getComment()->getId(),
            'flag_art_id' => $flag->getArticle()->getId(),
            'flag_usr_id' => $flag->getUser()->getId(),
            'flag_ip' => inet_pton($flag->getIp())
        );

        if ($flag->getId()) {
            // The flag has already been saved : update it
            $flagData['flag_date'] = $flag->getDate();
            $this->getDb()->update('t_flags', $flagData, array('flag_id' => $flag->getId()));
        }
        else {
            // The flag has never been saved : insert it
            $flagData['flag_date'] = (new \DateTime())->format('Y-m-d H:i:s');
            $this->getDb()->insert('t_flags', $flagData);

            // Get the id of the newly created flag and set it on the entity.
            $id = $this->getDb()->lastInsertId();
            $flag->setId($id);
        }
    }

    /**
     * Removes a flag from the database.
     *
     * @param integer $id The flag id
     */
    public function delete($id) {
        $this->getDb()->delete('t_flags', array('flag_id' => $id));
    }
}
