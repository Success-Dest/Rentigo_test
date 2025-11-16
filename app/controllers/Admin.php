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

        // Calculate active tenants (approved and active bookings)
        $activeBookings = array_filter($allBookings, fn($b) => $b->status === 'active' || $b->status === 'approved');

        // Calculate monthly revenue (10% platform service fee from rentals + 100% maintenance payments)
        $currentMonth = date('Y-m');
        $monthlyRevenue = 0;

        // Rental payment income (10% service fee)
        foreach ($allPayments as $payment) {
            if ($payment->status === 'completed' &&
                date('Y-m', strtotime($payment->payment_date)) === $currentMonth) {
                // Platform earns 10% service fee from each rental payment
                $monthlyRevenue += ($payment->amount * 0.10);
            }
        }

        // Maintenance payment income (100% - full payment amount)
        $maintenancePayments = $maintenanceQuotationModel->getAllMaintenancePayments();
        foreach ($maintenancePayments as $payment) {
            if ($payment->status === 'completed' &&
                date('Y-m', strtotime($payment->payment_date)) === $currentMonth) {
                // Platform earns 100% from maintenance payments
                $monthlyRevenue += $payment->amount;
            }
        }

        // Get recent properties (last 10)
        $recentProperties = array_slice($allProperties, -10);
        $recentProperties = array_reverse($recentProperties);

        $data = [
            'title' => 'Admin Dashboard - Rentigo',
            'page' => 'dashboard',
            'totalProperties' => count($allProperties),
            'activeTenants' => count($activeBookings),
            'monthlyRevenue' => $monthlyRevenue,
            'pendingApprovals' => count($pendingPMs),
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

    // Documents management page
    public function documents()
    {
        $data = [
            'title' => 'Documents - Rentigo Admin',
            'page' => 'documents'
        ];
        $this->view('admin/v_documents', $data);
    }

    // Financial management page
    public function financials()
    {
        // Load payment model
        $paymentModel = $this->model('M_Payments');
        $maintenanceModel = $this->model('M_Maintenance');

        // Get all payments
        $allPayments = $paymentModel->getAllPayments();

        // Calculate statistics (10% platform service fee from all payments)
        $totalRevenue = 0;
        $collected = 0;
        $pending = 0;
        $overdue = 0;
        $pendingCount = 0;
        $overdueCount = 0;

        foreach ($allPayments as $payment) {
            // Platform earns 10% service fee from each payment
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

        // Get recent transactions (payments and maintenance)
        $recentTransactions = array_slice($allPayments, -20);
        $recentTransactions = array_reverse($recentTransactions);

        $data = [
            'title' => 'Financials - Rentigo Admin',
            'page' => 'financials',
            'totalRevenue' => $totalRevenue,
            'collected' => $collected,
            'pending' => $pending,
            'overdue' => $overdue,
            'pendingCount' => $pendingCount,
            'overdueCount' => $overdueCount,
            'recentTransactions' => $recentTransactions
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
        // Load notification model if exists, otherwise use dummy data
        $notificationModel = $this->model('M_Notifications');

        // Get all notifications sent by admin
        $allNotifications = $notificationModel->getAllAdminNotifications();

        // Calculate statistics
        $totalSent = count($allNotifications);
        $delivered = count(array_filter($allNotifications, fn($n) => $n->status === 'sent' || $n->status === 'delivered'));
        $draft = count(array_filter($allNotifications, fn($n) => $n->status === 'draft'));

        // Calculate total recipients
        $totalRecipients = 0;
        foreach ($allNotifications as $notification) {
            $totalRecipients += $notification->recipient_count ?? 1;
        }

        $data = [
            'title' => 'Notifications - Rentigo Admin',
            'page' => 'notifications',
            'totalSent' => $totalSent,
            'delivered' => $delivered,
            'draft' => $draft,
            'totalRecipients' => $totalRecipients,
            'notifications' => $allNotifications
        ];
        $this->view('admin/v_notifications', $data);
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
}
