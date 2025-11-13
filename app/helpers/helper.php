<?php
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isTenant()
{
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'tenant';
}

function isLandlord()
{
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'landlord';
}

function isPropertyManager()
{
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'property_manager';
}

function isAdmin()
{
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function logout()
{
    // Clear all memory of the user
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_type']);

    session_destroy();

    // Redirect to login page
    redirect('users/login');
}
