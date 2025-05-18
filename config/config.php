<?php
// Base path

define('BASE_URL', '');
define("ROOT_PATH", dirname(__DIR__));
define("UPLOAD_PATH", ROOT_PATH . "/uploads");


// Images path
define("IMAGES_PATH", UPLOAD_PATH . "/images");

//Images URL
define("IMAGES_URL", BASE_URL . "/uploads/images");

// Pagination settings
define("ITEMS_PER_PAGE", 12);

//Session settings
define("SESSION_LIFETIME", 86400);

// Hash cost for password hashing
define("PASSWORD_HASH_COST", 12);

//redirect function
function redirect($url)
{
    header("Location: " . $url);
    exit;
}

// Function to form full URL
function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}