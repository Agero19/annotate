<?php

require_once ROOT_PATH . '/config/database.php';

class Image
{
    private $conn;
    private $table_name = "images";
    public $image_id;
    public $user_id;
    public $username;
    public $title;
    public $description;
    public $image_url;
    public $visibility;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        // SQL запит для створення нового туру
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id = :user_id,
                    username = :username,
                    title = :title,
                    description = :description,
                    image_url = :image_url,
                    visibility = :visibility,
                    created_at = NOW()";


        // Підготовка запиту
        $stmt = $this->conn->prepare($query);

        // Очищення даних
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->visibility = htmlspecialchars(strip_tags($this->visibility));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Прив'язка значень
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":visibility", $this->visibility);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image_url", $this->image_url);

        // Виконання запиту
        if ($stmt->execute()) {
            $this->tour_id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getPublicImages($limit = 12)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE visibility = TRUE ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }

    public function getUserImages($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }

    public function getImageById($image_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    public function deleteImage($image_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getImagesByTitle($title)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE title LIKE :title";
        $stmt = $this->conn->prepare($query);
        $title = "%" . htmlspecialchars(strip_tags($title)) . "%";
        $stmt->bindParam(':title', $title);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }
}