<?php

namespace Livre\DAO;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Livre\Domain\User;

class UserDAO extends DAO implements UserProviderInterface
{
	public function findAll() {
		$sql = "select *from t_user order by usr_role, usr_name";
		$result = $this->getDb()->fetchAll($sql);

        $entities = array();
        foreach ($result as $row) {
            $id = $row['usr_id'];
            $entities[$id] = $this->buildDomainObject($row);
        }
        return $entities;
    }
	
	public function find($id) {
		$sql = "select * from t_user where usr_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No user matching id " . $id);
    }
	
	public function loadUserByUsername($username)
	{
		$sql = "select * from t_user where usr_name=?";
		$row = $this->getDb()->fetchAssoc($sql, array($username));
		
		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new UsernameNotFoundException(sprintf('user "%s" not found.', $username));
	}
	
	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		if (!$this->supportsClass($class)) {
			throw new UnsupportedUserException(sprintf('Instance of "%s" are not supported.', $class));
		}
		return $this->loadUserByUsername($user->getUsername());
	}
	
	public function supportsClass($class)
	{
		return 'Livre\Domain\User' == $class;
	}
	
	function save(User $user) {
		$userData = array(
			'usr_name' => $user->getUsername(),
			'usr_salt' => $user->getSalt(),
			'usr_password' => $user->getPassword(),
			'usr_role' => $user->getRole()
			);
			
		if ($user ->getId()) {
			$this->getDb()->update('t_user', $userData, array('usr_id' => $user->getId()));
		} else {
			$this->getDb()->insert('t_user', $userData);
			$id = $this->getDb()->lastInsertId();
			$user->setId($id);
		}
	}
	
	public function delete($id) {
		$this->getDb()->delete('t_user', array('usr_id' =>$id));
	}
	
	protected function buildDomainObject(array $row) {
        $user = new User();
        $user->setId($row['usr_id']);
        $user->setUsername($row['usr_name']);
        $user->setPassword($row['usr_password']);
        $user->setSalt($row['usr_salt']);
        $user->setRole($row['usr_role']);
        return $user;
    }
}