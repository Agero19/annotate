<?php
require_once ROOT_PATH . '/config/database.php';

class Annotation
{
    private $conn;
    private $table_name = "annotations";

    public $annotation_id;
    public $image_id;
    public $user_id;
    public $x;
    public $y;
    public $width;
    public $height;
    public $comment;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                  SET image_id = :image_id,
                      user_id = :user_id,
                      x = :x,
                      y = :y,
                      width = :width,
                      height = :height,
                      comment = :comment,
                      created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Очистка данных
        $this->comment = htmlspecialchars(strip_tags($this->comment));

        $stmt->bindParam(':image_id', $this->image_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':x', $this->x, PDO::PARAM_INT);
        $stmt->bindParam(':y', $this->y, PDO::PARAM_INT);
        $stmt->bindParam(':width', $this->width, PDO::PARAM_INT);
        $stmt->bindParam(':height', $this->height, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $this->comment);

        return $stmt->execute();
    }

    public function getAnnotationsByImageId($image_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE image_id = :image_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }
    public function deleteAnnotation($annotation_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE annotation_id = :annotation_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':annotation_id', $annotation_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function getAnnotationById($annotation_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE annotation_id = :annotation_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':annotation_id', $annotation_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
