<?php
session_start();
require_once __DIR__ . '/../models/User.php';

function get_relative_root()
{
    // Simple heuristic: if we are in an 'admin' folder, go up one level.
    // Adjust this if you have deeper nesting.
    if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) {
        return '../';
    }
    return '';
}

function login($email, $password)
{
    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function logout()
{
    session_destroy();
    header("Location: " . get_relative_root() . "login.php");
    exit;
}

function current_user()
{
    if (isset($_SESSION['user_id'])) {
        $userModel = new User();
        return $userModel->findById($_SESSION['user_id']);
    }
    return null;
}

function require_login()
{
    if (!current_user()) {
        header("Location: " . get_relative_root() . "login.php");
        exit;
    }
}

function require_admin()
{
    $user = current_user();

    if (!$user) {
        header("Location: " . get_relative_root() . "login.php");
        exit;
    }

    if ($user['role'] !== 'ADMIN') {
        // TODO: Check relation tables if legacy column is empty
        echo "Access Denied";
        exit;
    }
}
