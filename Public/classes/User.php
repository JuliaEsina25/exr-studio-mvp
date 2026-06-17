<?php
class User
{
    private static $pdo = null;
    
    public static function setPDO($connection)
    {
        self::$pdo = $connection;
    }
    
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    public static function getCurrentUser()
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'] ?? ''
        ];
    }
    
    public static function login($id, $name, $email = '')
    {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
    }
    
    public static function logout()
    {
        session_destroy();
    }
}
