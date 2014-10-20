<?php

namespace tdt4237\webapp\models;

use tdt4237\webapp\models\Avatar;
use tdt4237\webapp\Hash;

class User extends Avatar
{  
    const INSERT_QUERY = "INSERT INTO users(user, salt, pass, email, age, bio, avatar, isadmin) VALUES('%s', '%s', '%s', '%s' , '%s' , '%s', '%s', '%s')";
    const UPDATE_QUERY = "UPDATE users SET email=?, age=?, bio=?, avatar=?, isAdmin=? WHERE id=?";
    //const FIND_BY_NAME = "SELECT * FROM users WHERE user=?";
    const FIND_BY_NAME = "SELECT * FROM users WHERE user='%s'";

    const MIN_USER_LENGTH = 3;    
    const MAX_USER_LENGTH = 15;

    const MIN_PASSWORD_LENGTH = 8;

    protected $id = null;
    protected $user;
    protected $salt;
    protected $pass;
    protected $email;
    protected $bio = 'Bio is empty.';
    protected $age;

    protected $isAdmin = 0;

    static $app;

    function __construct()
    {
    }


    static function make($id, $username, $salt, $hash, $email, $bio, $age, $avatar, $isAdmin)
    {
        $user = new User();
        $user->id = $id;
        $user->user = $username;
        $user->salt = $salt;
        $user->pass = $hash;
        $user->email = $email;
        $user->bio = $bio;
        $user->age = $age;
        $user->avatar = $avatar;
        
        $user->isAdmin = $isAdmin;

        return $user;
    }

    static function makeEmpty()
    {
        return new User();
    }

    /**
     * Insert or update a user object to db.
     */
    function save()
    {
        if ($this->id === null) {
            $query = sprintf(self::INSERT_QUERY,
                $this->user,
                $this->salt,
                $this->pass,
                $this->email,
                $this->age,
                $this->bio,
                $this->avatar,
                    
                $this->isAdmin
            );
            return self::$app->db->exec($query);
        } else {
//            $query = sprintf(self::UPDATE_QUERY,
//                $this->email,
//                $this->age,
//                $this->bio,
//                $this->avatar,
//                    
//                $this->isAdmin,
//                $this->id
//            );
            
            $query = self::$app->db->prepare(self::UPDATE_QUERY);
            $result = $query->execute(array($this->email, $this->age, $this->bio, $this->avatar, $this->isAdmin, $this->id));
        }
        //return $query->execute(array($this->email, $this->age, $this->bio, $this->avatar, $this->isAdmin, $this->id));
        //return self::$app->db->exec($query);
        return $result;
    }

    function getId()
    {
        return $this->id;
    }

    function getUserName()
    {
        return $this->user;
    }

    function getPasswordHash()
    {
        return $this->pass;
    }

    function getSalt() {
        return $this->salt;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getBio()
    {
        return $this->bio;
    }

    function getAge()
    {
        return $this->age;
    }
    
    function isAdmin()
    {
        return $this->isAdmin === "1";
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setUsername($username)
    {
        $this->user = $username;
    }

    function setHash($hash)
    {
        $this->pass = $hash;
    }

    function setSalt($salt) 
    {
        $this->salt = $salt;
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setBio($bio)
    {
        $this->bio = $bio;
    }

    function setAge($age)
    {
        $this->age = $age;
    }
   
    /**
     * The caller of this function can check the length of the returned 
     * array. If array length is 0, then all checks passed.
     *
     * @param User $user
     * @return array An array of strings of validation errors
     */
    static function validate(User $user, $pass)
    {
        $validationErrors = [];

        if (strlen($user->user) < self::MIN_USER_LENGTH) {
            array_push($validationErrors, "Username too short. Min length is " . self::MIN_USER_LENGTH);
        }

        $uppercase = preg_match('@[A-Z]@', $pass);
        $number    = preg_match('@[0-9]@', $pass);

        if (strlen($user->user) > self::MAX_USER_LENGTH) {
            array_push($validationErrors, "Username too long. Max length is " . self::MAX_USER_LENGTH);
        }

        if (strlen($pass) < self::MIN_PASSWORD_LENGTH) {
            array_push($validationErrors, "Password is too short. Minimum length is " . self::MIN_PASSWORD_LENGTH);
        }

        if (!$uppercase) {
            array_push($validationErrors, "Password must contain at least one uppcase letter!");
        }

        if (!$number) {
            array_push($validationErrors, "Password must contain at least one number!");
        }

        if (preg_match('/^[A-Za-z0-9_]+$/', $user->user) === 0) {
            array_push($validationErrors, 'Username can only contain letters and numbers');
        }

        return $validationErrors;
    }
    
    static function validatePass($pass)
    {
        $validationErrors = [];

        $uppercase = preg_match('@[A-Z]@', $pass);
        $number    = preg_match('@[0-9]@', $pass);

        if (strlen($pass) < self::MIN_PASSWORD_LENGTH) {
            array_push($validationErrors, "Password is too short. Minimum length is " . self::MIN_PASSWORD_LENGTH);
        }

        if (!$uppercase) {
            array_push($validationErrors, "Password must contain at least one uppcase letter!");
        }

        if (!$number) {
            array_push($validationErrors, "Password must contain at least one number!");
        }
        
        return $validationErrors;
    }

    static function validateAge(User $user)
    {
        $age = $user->getAge();

        if ($age >= 0 && $age <= 150) {
            return true;
        }

        return false;
    }

    /**
     * Find user in db by username.
     *
     * @param string $username
     * @return mixed User or null if not found.
     */
    static function findByUser($username)
    {
//        $q = self::$app->db->prepare(self::FIND_BY_NAME);
//        $q->execute(array($username));
//        $row = $q->setFetchMode(\PDO::FETCH_ASSOC);
        $query = sprintf(self::FIND_BY_NAME, $username);
        $result = self::$app->db->query($query, \PDO::FETCH_ASSOC);
        $row = $result->fetch();
        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }

    /**
    * Find the users salt in db by username
    *
    * @param string $username
    * @return the users salt
    */
    static function findSaltByUser($username) {
        $query = sprintf(self::FIND_BY_NAME, $username);
        $result = self::$app->db->query($query, \PDO::FETCH_ASSOC);
        $row = $result->fetch();

        if($row == false) {
            return null;
        }
        return $row['salt'];
    }

    static function deleteByUsername($username)
    {
        $query = "DELETE FROM users WHERE user='$username' ";
        return self::$app->db->exec($query);
    }

    static function all()
    {
        $query = "SELECT * FROM users";
        $results = self::$app->db->query($query);

        $users = [];

        foreach ($results as $row) {
            $user = User::makeFromSql($row);
            array_push($users, $user);
        }

        return $users;
    }

    static function makeFromSql($row)
    {
        return User::make(
            $row['id'],
            $row['user'],
            $row['salt'],
            $row['pass'],
            $row['email'],
            $row['bio'],
            $row['age'],
            $row['avatar'],
            $row['isadmin']
        );
    }
}
User::$app = \Slim\Slim::getInstance();
