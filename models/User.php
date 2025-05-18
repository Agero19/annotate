<?php

require_once ROOT_PATH . '/config/database.php';

// Model class for User
class User
{
    // Object properties
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $email;
    public $password;
    public $avatar;
    public $registration_date;
    public $last_login;
    public $is_active;

    // class conctructor 
    //parameters : object $db - database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method to create a new user
    // returns : boolean
    public function create()
    {
        // SQL query to insert new user
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username = :username, 
                      email = :email, 
                      password = :password,  
                      avatar = :avatar,
                      is_active = :is_active";

        // preparation
        $stmt = $this->conn->prepare($query);

        // Cleaning
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->avatar = htmlspecialchars(strip_tags($this->avatar));

        // Pwd hashing
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);

        // Binding values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":avatar", $this->avatar);
        $stmt->bindParam(":is_active", $this->is_active);

        // Execution
        if ($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function emailOrUsernameExists($email, $username)
    {
        // SQL request to check
        $query = "SELECT user_id, username, email, password
                  FROM " . $this->table_name . "
                  WHERE email = :email OR username = :username
                  LIMIT 0,1";

        // Prepare
        $stmt = $this->conn->prepare($query);

        // Binding values
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":username", $username);

        // Execution
        $stmt->execute();

        // Check if any row exists
        if ($stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * User login method
     * @param string $username Username or email
     * @param string $password password
     * @return boolean
     */
    public function login($username, $password)
    {
        // SQL request to check user
        $query = "SELECT user_id, username, email, password, is_active
                  FROM " . $this->table_name . "
                  WHERE (username = :username OR email = :username)
                  LIMIT 0,1";

        // prepare
        $stmt = $this->conn->prepare($query);

        // Binding
        $stmt->bindParam(":username", $username);

        // Execution
        $stmt->execute();

        // Checking if any row exists
        if ($stmt->rowCount() > 0) {
            // Getting data
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user is active
            if (!$row['is_active']) {
                return false;
            }

            // Check password
            if (password_verify($password, $row['password'])) {
                // Update last login time
                $this->updateLastLogin($row['user_id']);

                // Setting object properties
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->is_active = $row['is_active'];

                return true;
            }
        }

        return false;
    }

    /**
     * Update last login time
     * @param int $user_id ID user
     * @return boolean
     */
    private function updateLastLogin($user_id)
    {
        // SQL query to update last login time
        $query = "UPDATE " . $this->table_name . "
                  SET last_login = NOW()
                  WHERE user_id = :user_id";

        // Prepare 
        $stmt = $this->conn->prepare($query);

        // Bind
        $stmt->bindParam(":user_id", $user_id);

        // Execute
        return $stmt->execute();
    }

    /**
     * Отримання користувача за ID
     * @param int $user_id ID користувача
     * @return boolean
     */
    public function readOne($user_id)
    {
        // SQL query to get user info
        $query = "SELECT *
                  FROM " . $this->table_name . "
                  WHERE user_id = :user_id
                  LIMIT 0,1";

        // Prepare
        $stmt = $this->conn->prepare($query);

        // Binding
        $stmt->bindParam(":user_id", $user_id);

        // Execute
        $stmt->execute();

        // Check if any row exists
        if ($stmt->rowCount() > 0) {
            // Get data
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Setting object properties
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->avatar = $row['avatar'];
            $this->registration_date = $row['registration_date'];
            $this->last_login = $row['last_login'];
            $this->is_active = $row['is_active'];

            return true;
        }

        return false;
    }

    /**
     * Оновлення інформації про користувача
     * @return boolean
     */
    public function update()
    {
        // SQL запит для оновлення інформації
        $query = "UPDATE " . $this->table_name . "
                  SET avatar = :avatar,
                      is_active = :is_active
                  WHERE user_id = :user_id";

        // Prepare
        $stmt = $this->conn->prepare($query);

        // Очищення даних
        $this->avatar = htmlspecialchars(strip_tags($this->avatar));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Binding
        $stmt->bindParam(":avatar", $this->avatar);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Оновлення паролю користувача
     * @param string $new_password Новий пароль
     * @return boolean
     */
    public function updatePassword($new_password)
    {
        // SQL запит для оновлення паролю
        $query = "UPDATE " . $this->table_name . "
                  SET password = :password
                  WHERE user_id = :user_id";

        // Prepare
        $stmt = $this->conn->prepare($query);

        // Хешування паролю
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);

        // Binding
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Видалення користувача
     * @return boolean
     */
    public function delete()
    {
        // SQL запит для видалення користувача
        $query = "DELETE FROM " . $this->table_name . "
                  WHERE user_id = :user_id";

        // Prepare
        $stmt = $this->conn->prepare($query);

        // Binding
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Отримання списку користувачів з пагінацією
     * @param int $page Номер сторінки
     * @param int $items_per_page Кількість записів на сторінку
     * @return array
     */
    public function readAll($page = 1, $items_per_page = ITEMS_PER_PAGE)
    {
        // Обчислення зміщення для пагінації
        $offset = ($page - 1) * $items_per_page;

        // SQL запит для отримання списку користувачів
        $query = "SELECT *
                  FROM " . $this->table_name . "
                  ORDER BY registration_date DESC
                  LIMIT :offset, :limit";

        // Prepare
        $stmt = $this->conn->prepare($query);

        // Binding
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $items_per_page, PDO::PARAM_INT);

        // Execute
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Отримання загальної кількості користувачів
     * @return int
     */
    public function countAll()
    {
        // SQL запит для отримання кількості користувачів
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->table_name;

        // Prepare
        $stmt = $this->conn->prepare($query);

        // Execute
        $stmt->execute();

        // Get data
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * Скидання паролю та надсилання листа користувачу
     * @param string $email Email користувача
     * @return boolean
     */
    public function resetPassword($email)
    {
        // Перевірка існування email
        $query = "SELECT user_id, first_name, email 
                 FROM " . $this->table_name . "
                 WHERE email = :email AND is_active = 1
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Генерація тимчасового паролю
            $temp_password = bin2hex(random_bytes(4));

            // Оновлення паролю в базі даних
            $this->user_id = $row['user_id'];
            if ($this->updatePassword($temp_password)) {
                //TODO: Code to send email with password update link
                return true;
            }
        }

        return false;
    }
}