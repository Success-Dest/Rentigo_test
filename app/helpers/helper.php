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

/**
 * Generates SQL condition for a rolling date range
 * @param string $columnName The database column to filter
 * @param int $days Number of days (default 30)
 * @return string SQL snippet
 */
function getDateRangeSql($columnName, $days = 30)
{
    return " $columnName >= DATE_SUB(CURDATE(), INTERVAL $days DAY) ";
}

/**
 * Generic helper to get statistics for the last 30 days
 * @param object $db Database object
 * @param string $table Table name
 * @param string $dateColumn Date column name
 * @param string $statType 'count' or 'sum'
 * @param string|null $sumColumn Column to sum if type is 'sum'
 * @param string $where Extra where conditions
 * @param int $days Number of days
 * @return mixed
 */
function get30DayStat($db, $table, $dateColumn, $statType = 'count', $sumColumn = null, $where = '', $days = 30)
{
    $dateFilter = getDateRangeSql($dateColumn, $days);
    $sql = "SELECT ";
    
    if ($statType === 'count') {
        $sql .= "COUNT(*) as stat";
    } else {
        $sql .= "SUM($sumColumn) as stat";
    }
    
    $sql .= " FROM $table WHERE $dateFilter";
    
    if (!empty($where)) {
        $sql .= " AND ($where)";
    }
    
    $db->query($sql);
    $result = $db->single();
    
    return $result ? ($result->stat ?? 0) : 0;
}
