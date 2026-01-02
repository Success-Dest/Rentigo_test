<?php
require_once '../app/helpers/helper.php';

class Admin extends Controller
{
    private $userModel;

    public function __construct()
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'admin') {
            redirect('users/login');
        }
        $this->userModel = $this->model('M_Users');
    }

    // Main dashboard page
    public function index()
    {
        // Load models for dashboard data
        $propertyModel = $this->model('M_Properties');
        $bookingModel = $this->model('M_Bookings');
        $paymentModel = $this->model('M_Payments');
        $maintenanceQuotationModel = $this->model('M_MaintenanceQuotations');

        // Get statistics
        $allProperties = $propertyModel->getAllProperties();
        $allBookings = $bookingModel->getAllBookings();
        $allPayments = $paymentModel->getAllPayments();
        $pendingPMs = $this->userModel->getPendingPMs();
        // Calculate 30-day revenue (10% platform service fee from rentals + 100% maintenance payments)
        $rentalRevenue = $paymentModel->getPlatformRentalIncome(30);
        $maintenanceRevenue = $maintenanceQuotationModel->getTotalMaintenanceIncome(30);
        $total30DayRevenue = $rentalRevenue + $maintenanceRevenue;

        // Calculate active tenants (approved and active bookings created in last 30 days)
        $activeBookings = array_filter($allBookings, function($b) {
            $isCorrectStatus = ($b->status === 'active' || $b->status === 'approved');
            $isWithin30Days = strtotime($b->created_at) >= strtotime('-30 days');
            return $isCorrectStatus && $isWithin30Days;
        });

        // Filter total properties (last 30 days)
        $propertiesLast30Days = array_filter($allProperties, function($p) {
            return strtotime($p->created_at) >= strtotime('-30 days');
        });

        // Filter pending approvals (last 30 days)
        $pendingApprovalsLast30Days = array_filter($pendingPMs, function($pm) {
            return strtotime($pm->created_at) >= strtotime('-30 days');
        });

        // Get recent properties (last 10)
        $recentProperties = array_slice($allProperties, -10);
        $recentProperties = array_reverse($recentProperties);

        $data = [
            'title' => 'Admin Dashboard - Rentigo',
            'page' => 'dashboard',
            'totalProperties' => count($propertiesLast30Days),
            'activeTenants' => count($activeBookings),
            'monthlyRevenue' => $total30DayRevenue,
            'pendingApprovals' => count($pendingApprovalsLast30Days),
            'recentProperties' => $recentProperties
        ];
        $this->view('admin/v_dashboard', $data);
    }

    // Properties management page
    public function properties()
    {
        redirect('AdminProperties/index');
    }

    // Property managers page
    public function managers()
    {
        $allManagers = $this->userModel->getAllPropertyManagers();

        $data = [
            'title' => 'Property Managers - Rentigo Admin',
            'page' => 'managers',
            'allManagers' => $allManagers
        ];
        $this->view('admin/v_managers', $data);
    }

    // Financial management page
    public function financials()
    {
        // Load payment models
        $paymentModel = $this->model('M_Payments');
        $maintenanceQuotationsModel = $this->model('M_MaintenanceQuotations');

        // Get all rental payments
        $allPayments = $paymentModel->getAllPayments();

        // Get all maintenance payments
        $maintenancePayments = $maintenanceQuotationsModel->getAllMaintenancePayments();

        // Calculate statistics (10% platform service fee from all payments)
        $totalRevenue = 0;
        $collected = 0;
        $pending = 0;
        $overdue = 0;
        $pendingCount = 0;
        $overdueCount = 0;

        // Calculate rental payment fees (10% service fee) - Filtered by 30 days for stats
        foreach ($allPayments as $payment) {
            $date = $payment->payment_date ?? $payment->created_at;
            if (strtotime($date) >= strtotime('-30 days')) {
                // Platform earns 10% service fee from each rental payment
                $totalRevenue += ($payment->amount * 0.10);
                if ($payment->status === 'completed') {
                    $collected += ($payment->amount * 0.10);
                } elseif ($payment->status === 'pending') {
                    $pending += ($payment->amount * 0.10);
                    $pendingCount++;
                } elseif ($payment->status === 'overdue') {
                    $overdue += ($payment->amount * 0.10);
                    $overdueCount++;
                }
            }
        }

        // Calculate maintenance payment income (100% goes to platform) - Filtered by 30 days for stats
        foreach ($maintenancePayments as $payment) {
            $date = $payment->payment_date ?? $payment->created_at;
            if (strtotime($date) >= strtotime('-30 days')) {
                // Platform receives full maintenance payment amount as income
                $totalRevenue += $payment->amount;
                if ($payment->status === 'completed') {
                    $collected += $payment->amount;
                } elseif ($payment->status === 'pending') {
                    $pending += $payment->amount;
                    $pendingCount++;
                } elseif ($payment->status === 'failed') {
                    // Treat failed as overdue for maintenance
                    $overdue += $payment->amount;
                    $overdueCount++;
                }
            }
        }

        // Merge and sort all transactions by date
        $allTransactions = array_merge($allPayments, $maintenancePayments);

        // Sort by payment_date (descending)
        usort($allTransactions, function($a, $b) {
            $dateA = strtotime($a->payment_date ?? $a->due_date ?? $a->created_at);
            $dateB = strtotime($b->payment_date ?? $b->due_date ?? $b->created_at);
            return $dateB - $dateA;
        });

        // ==================== APPLY FILTERS ====================
        $filteredTransactions = $allTransactions;
        
        // Get filter parameters
        $filterType = $_GET['filter_type'] ?? '';
        $filterStatus = $_GET['filter_status'] ?? '';
        $filterDateFrom = $_GET['filter_date_from'] ?? '';
        $filterDateTo = $_GET['filter_date_to'] ?? '';

        // Apply Type Filter
        if (!empty($filterType)) {
            $filteredTransactions = array_filter($filteredTransactions, function($transaction) use ($filterType) {
                $isMaintenance = isset($transaction->payment_type) && $transaction->payment_type === 'maintenance';
                
                if ($filterType === 'rental') {
                    return !$isMaintenance;
                } elseif ($filterType === 'maintenance') {
                    return $isMaintenance;
                }
                return true;
            });
        }

        // Apply Status Filter
        if (!empty($filterStatus)) {
            $filteredTransactions = array_filter($filteredTransactions, function($transaction) use ($filterStatus) {
                return strtolower($transaction->status) === strtolower($filterStatus);
            });
        }

        // Apply Date Range Filter
        if (!empty($filterDateFrom) || !empty($filterDateTo)) {
            $filteredTransactions = array_filter($filteredTransactions, function($transaction) use ($filterDateFrom, $filterDateTo) {
                $displayDate = $transaction->payment_date ?? $transaction->due_date ?? $transaction->created_at;
                $transactionDate = strtotime($displayDate);
                
                // Check FROM date
                if (!empty($filterDateFrom)) {
                    $fromDate = strtotime($filterDateFrom);
                    if ($transactionDate < $fromDate) {
                        return false;
                    }
                }
                
                // Check TO date
                if (!empty($filterDateTo)) {
                    $toDate = strtotime($filterDateTo . ' 23:59:59'); // End of day
                    if ($transactionDate > $toDate) {
                        return false;
                    }
                }
                
                return true;
            });
        }

        // Re-index array after filtering
        $filteredTransactions = array_values($filteredTransactions);

        $data = [
            'title' => 'Financials - Rentigo Admin',
            'page' => 'financials',
            'totalRevenue' => $totalRevenue,
            'collected' => $collected,
            'pending' => $pending,
            'overdue' => $overdue,
            'pendingCount' => $pendingCount,
            'overdueCount' => $overdueCount,
            'recentTransactions' => $filteredTransactions,
            
            // Pass filter values back to view for persistence
            'filter_type' => $filterType,
            'filter_status' => $filterStatus,
            'filter_date_from' => $filterDateFrom,
            'filter_date_to' => $filterDateTo,
        ];
        $this->view('admin/v_financials', $data);
    }

    public function providers()
    {
        redirect('providers/index');
    }

    // Policies management page
    public function policies()
    {
        redirect('policies/index');
    }

    // Notifications page
    public function notifications()
    {
        // Load notification model
        $notificationModel = $this->model('M_Notifications');

        // Get admin-sent notifications and other notifications separately
        $adminNotifications = $notificationModel->getAdminSentNotifications();
        $otherNotifications = $notificationModel->getOtherNotifications();

        // Get statistics
        $stats = $notificationModel->getNotificationStats();

        $data = [
            'title' => 'Notifications - Rentigo Admin',
            'page' => 'notifications',
            'adminNotifications' => $adminNotifications,
            'otherNotifications' => $otherNotifications,
            'stats' => $stats
        ];
        $this->view('admin/v_notifications', $data);
    }

    // Send notification form page
    public function sendNotification()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            // Validate inputs
            $errors = [];
            $recipient_type = $_POST['recipient_type'] ?? '';
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (empty($recipient_type)) {
                $errors[] = 'Please select recipient type';
            }
            if (empty($title)) {
                $errors[] = 'Please enter notification title';
            }
            if (empty($message)) {
                $errors[] = 'Please enter notification message';
            }

            if (empty($errors)) {
                $notificationModel = $this->model('M_Notifications');

                // Get user IDs based on recipient type
                $recipients = [];
                switch ($recipient_type) {
                    case 'all':
                        $recipients = array_merge(
                            $this->userModel->getAllUsersByType('tenant'),
                            $this->userModel->getAllUsersByType('landlord'),
                            $this->userModel->getAllUsersByType('property_manager')
                        );
                        break;
                    case 'tenants':
                        $recipients = $this->userModel->getAllUsersByType('tenant');
                        break;
                    case 'landlords':
                        $recipients = $this->userModel->getAllUsersByType('landlord');
                        break;
                    case 'managers':
                        $recipients = $this->userModel->getAllUsersByType('property_manager');
                        break;
                }

                // Send notification to each recipient
                $sent_count = 0;
                foreach ($recipients as $user) {
                    $notificationModel->createNotification([
                        'user_id' => $user->id,
                        'type' => 'system',
                        'title' => $title,
                        'message' => $message,
                        'link' => ''
                    ]);
                    $sent_count++;
                }

                flash('notification_message', "Successfully sent notification to $sent_count user(s)", 'alert alert-success');
                redirect('admin/notifications');
            } else {
                $data = [
                    'title' => 'Send Notification - Rentigo Admin',
                    'page' => 'notifications',
                    'errors' => $errors,
                    'recipient_type' => $recipient_type,
                    'notification_title' => $title,
                    'notification_message' => $message
                ];
                $this->view('admin/v_send_notification', $data);
            }
        } else {
            $data = [
                'title' => 'Send Notification - Rentigo Admin',
                'page' => 'notifications',
                'errors' => [],
                'recipient_type' => '',
                'notification_title' => '',
                'notification_message' => ''
            ];
            $this->view('admin/v_send_notification', $data);
        }
    }

    // Property Manager approvals page
    public function pm_approvals()
    {
        $pendingPMs = $this->userModel->getPendingPMs();

        $data = [
            'title' => 'PM Approvals - Rentigo Admin',
            'page' => 'pm_approvals',
            'pending_pms' => $pendingPMs
        ];
        $this->view('admin/v_pm_approvals', $data);
    }

    // Approve Property Manager
    public function approvePM($userId)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            error_log("ApprovePM called with userId: " . $userId);

            if (!isLoggedIn()) {
                error_log("User not logged in");
                echo json_encode([
                    'success' => false,
                    'message' => 'You are not logged in'
                ]);
                exit();
            }

            if ($_SESSION['user_type'] !== 'admin') {
                error_log("User is not admin: " . $_SESSION['user_type']);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin role required.'
                ]);
                exit();
            }

            error_log("Attempting to approve PM with ID: " . $userId);

            try {
                $result = $this->userModel->approvePM($userId);
                error_log("ApprovePM result: " . ($result ? 'true' : 'false'));

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Property Manager approved successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to approve Property Manager. Database update returned false.'
                    ]);
                }
            } catch (Exception $e) {
                error_log("Exception in approvePM: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
            exit();
        } else {
            redirect('admin/managers');
        }
    }

    // Remove Property Manager
    public function removePropertyManager($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            if (!isLoggedIn()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You are not logged in'
                ]);
                exit();
            }

            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin role required.'
                ]);
                exit();
            }

            if ($this->userModel->removePropertyManager($userId)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Property Manager removed successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to remove Property Manager'
                ]);
            }
            exit();
        } else {
            redirect('admin/managers');
        }
    }

    // Reject Property Manager
    public function rejectPM($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            if (!isLoggedIn() || $_SESSION['user_type'] !== 'admin') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
                exit();
            }

            $adminId = $_SESSION['user_id'];

            if ($this->userModel->rejectPM($userId, $adminId)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Property Manager application rejected'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to reject application'
                ]);
            }
            exit();
        } else {
            redirect('admin/managers');
        }
    }

    // View all inspections from all PMs
    public function inspections()
    {
        $inspectionModel = $this->model('M_Inspection');
        $allInspections = $inspectionModel->getAllInspections();

        // ==================== APPLY FILTERS ====================
        $filteredInspections = $allInspections;
        
        // Get filter parameters
        $filterStatus = $_GET['filter_status'] ?? '';
        $filterType = $_GET['filter_type'] ?? '';
        $filterDateFrom = $_GET['filter_date_from'] ?? '';
        $filterDateTo = $_GET['filter_date_to'] ?? '';

        // Apply Status Filter
        if (!empty($filterStatus)) {
            $filteredInspections = array_filter($filteredInspections, function($inspection) use ($filterStatus) {
                return strtolower($inspection->status) === strtolower($filterStatus);
            });
        }

        // Apply Type Filter
        if (!empty($filterType)) {
            $filteredInspections = array_filter($filteredInspections, function($inspection) use ($filterType) {
                return strtolower($inspection->type) === strtolower($filterType);
            });
        }

        // Apply Date Range Filter (on scheduled_date)
        if (!empty($filterDateFrom) || !empty($filterDateTo)) {
            $filteredInspections = array_filter($filteredInspections, function($inspection) use ($filterDateFrom, $filterDateTo) {
                $scheduledDate = strtotime($inspection->scheduled_date);
                
                // Check FROM date
                if (!empty($filterDateFrom)) {
                    $fromDate = strtotime($filterDateFrom);
                    if ($scheduledDate < $fromDate) {
                        return false;
                    }
                }
                
                // Check TO date
                if (!empty($filterDateTo)) {
                    $toDate = strtotime($filterDateTo . ' 23:59:59'); // End of day
                    if ($scheduledDate > $toDate) {
                        return false;
                    }
                }
                
                return true;
            });
        }

        // Re-index array after filtering
        $filteredInspections = array_values($filteredInspections);

        $data = [
            'title' => 'All Inspections',
            'page' => 'inspections',
            'user_name' => $_SESSION['user_name'],
            'inspections' => $filteredInspections,
            
            // Pass filter values back to view for persistence
            'filter_status' => $filterStatus,
            'filter_type' => $filterType,
            'filter_date_from' => $filterDateFrom,
            'filter_date_to' => $filterDateTo,
        ];

        $this->view('admin/v_inspections', $data);
    }
}
