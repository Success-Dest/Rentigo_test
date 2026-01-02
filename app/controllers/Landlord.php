<?php
require_once '../app/helpers/helper.php';

class Landlord extends Controller
{
    private $userModel;
    private $bookingModel;
    private $paymentModel;
    private $leaseModel;
    private $propertyModel;
    private $maintenanceModel;
    private $notificationModel;
    private $reviewModel;

    public function __construct()
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'landlord') {
            redirect('users/login');
        }

        $this->userModel = $this->model('M_Users');
        $this->bookingModel = $this->model('M_Bookings');
        $this->paymentModel = $this->model('M_Payments');
        $this->leaseModel = $this->model('M_LeaseAgreements');
        $this->propertyModel = $this->model('M_Properties');
        $this->maintenanceModel = $this->model('M_Maintenance');
        $this->notificationModel = $this->model('M_Notifications');
        $this->reviewModel = $this->model('M_Reviews');
    }

    // Helper method to get unread notification count
    private function getUnreadNotificationCount()
    {
        return $this->notificationModel->getUnreadCount($_SESSION['user_id']);
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        // Get dashboard statistics - Filtered by 30 days (Models already updated)
        $propertyStats = $this->propertyModel->getPropertyStatsByLandlord($_SESSION['user_id']);
        $bookingStats = $this->bookingModel->getBookingStats($_SESSION['user_id'], 'landlord');
        $pendingBookings = $this->bookingModel->getPendingBookingsCount($_SESSION['user_id']);
        $totalIncome = $this->paymentModel->getTotalIncomeByLandlord($_SESSION['user_id']);
        $activeLeases = $this->leaseModel->getActiveLeasesCount($_SESSION['user_id']);
        $pendingMaintenance = $this->maintenanceModel->getPendingMaintenanceCount($_SESSION['user_id']);
        
        // Filter recent bookings by last 30 days
        $allRecentBookings = $this->bookingModel->getBookingsByLandlord($_SESSION['user_id']);
        $recentBookings = array_filter($allRecentBookings, function($b) {
            return strtotime($b->created_at ?? '') >= strtotime('-30 days');
        });

        // Filter recent payments by last 30 days
        $allRecentPayments = $this->paymentModel->getRecentPayments($_SESSION['user_id'], 'landlord', 10);
        $recentPayments = array_filter($allRecentPayments, function($p) {
            $date = $p->payment_date ?? $p->created_at;
            return strtotime($date) >= strtotime('-30 days');
        });

        $unreadNotifications = $this->notificationModel->getUnreadCount($_SESSION['user_id']);

        // Get issue statistics (Updated in model)
        $issueModel = $this->model('M_Issue');
        $issueStats = $issueModel->getIssueStats($_SESSION['user_id'], 'landlord');

        // Limit recent bookings to 5
        if (count($recentBookings) > 5) {
            $recentBookings = array_slice($recentBookings, 0, 5);
        }
        
        // Limit recent payments to 5
        if (count($recentPayments) > 5) {
            $recentPayments = array_slice($recentPayments, 0, 5);
        }

        $data = [
            'title' => 'Landlord Dashboard',
            'page' => 'dashboard',
            'user_name' => $_SESSION['user_name'],
            'propertyStats' => $propertyStats,
            'bookingStats' => $bookingStats,
            'pendingBookings' => $pendingBookings,
            'totalIncome' => $totalIncome,
            'activeLeases' => $activeLeases,
            'pendingMaintenance' => $pendingMaintenance,
            'recentBookings' => $recentBookings,
            'recentPayments' => $recentPayments,
            'unreadNotifications' => $unreadNotifications,
            'issueStats' => $issueStats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('landlord/v_dashboard', $data);
    }

    public function properties()
    {
        redirect('properties/index');
    }

    public function maintenance()
    {
        redirect('maintenance/index');
    }

    public function bookings()
    {
        // Get all bookings for landlord's properties
        $bookings = $this->bookingModel->getBookingsByLandlord($_SESSION['user_id']);
        $bookingStats = $this->bookingModel->getBookingStats($_SESSION['user_id'], 'landlord');

        $data = [
            'title' => 'Property Bookings',
            'page' => 'bookings',
            'user_name' => $_SESSION['user_name'],
            'bookings' => $bookings,
            'bookingStats' => $bookingStats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('landlord/v_bookings', $data);
    }

    public function inquiries()
    {
        $issueModel = $this->model('M_Issue');
        $landlord_id = $_SESSION['user_id'];

        // Get all issues for this landlord's properties
        $allIssues = $issueModel->getIssuesByLandlord($landlord_id);

        // Filter by status
        $pendingIssues = array_filter($allIssues, fn($issue) => $issue->status === 'pending');
        $inProgressIssues = array_filter($allIssues, fn($issue) => $issue->status === 'in_progress');
        $resolvedIssues = array_filter($allIssues, fn($issue) => $issue->status === 'resolved');

        // Get issue statistics
        $stats = $issueModel->getIssueStats($landlord_id, 'landlord');

        $data = [
            'title' => 'Tenant Inquiries',
            'page' => 'inquiries',
            'user_name' => $_SESSION['user_name'],
            'allIssues' => $allIssues,
            'pendingIssues' => $pendingIssues,
            'inProgressIssues' => $inProgressIssues,
            'resolvedIssues' => $resolvedIssues,
            'issueStats' => $stats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('landlord/v_inquiries', $data);
    }

    public function payment_history()
    {
        // Get all payments for landlord (Full history for table)
        $payments = $this->paymentModel->getPaymentsByLandlord($_SESSION['user_id']);
        
        // Get summary statistics for last 30 days
        $totalIncome = $this->paymentModel->getTotalIncomeByLandlord($_SESSION['user_id']);
        $paymentStats = $this->paymentModel->getPaymentStatsByLandlord($_SESSION['user_id']);

        // Filter payments for 30-day stat cards
        $recentPayments = array_filter($payments, function($p) {
            $date = $p->payment_date ?? $p->created_at;
            return strtotime($date) >= strtotime('-30 days');
        });

        $data = [
            'title' => 'Payment History',
            'page' => 'payment_history',
            'user_name' => $_SESSION['user_name'],
            'payments' => $payments, // Full history
            'recentPayments' => $recentPayments, // 30-day filtered for cards
            'totalIncome' => (object)['total_income' => $totalIncome],
            'paymentStats' => $paymentStats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('landlord/v_payment_history', $data);
    }

    public function feedback()
    {
        // Get all reviews about landlord's properties
        $myReviews = $this->reviewModel->getReviewsByReviewer($_SESSION['user_id']);
        $reviewsAboutMe = $this->reviewModel->getReviewsAboutUser($_SESSION['user_id'], 'tenant');

        $data = [
            'title' => 'Tenant Feedback',
            'page' => 'feedback',
            'user_name' => $_SESSION['user_name'],
            'myReviews' => $myReviews,
            'reviewsAboutMe' => $reviewsAboutMe,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('landlord/v_feedback', $data);
    }

    public function notifications()
    {
        // Get all notifications for the landlord
        $notifications = $this->notificationModel->getNotificationsByUser($_SESSION['user_id']);
        $unreadCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);

        $data = [
            'title' => 'Notifications',
            'page' => 'notifications',
            'user_name' => $_SESSION['user_name'],
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('landlord/v_notifications', $data);
    }

    public function settings()
    {
        // Get user details
        $user = $this->userModel->getUserById($_SESSION['user_id']);

        $data = [
            'title' => 'Settings',
            'page' => 'settings',
            'user_name' => $_SESSION['user_name'],
            'user' => $user,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('landlord/v_settings', $data);
    }

    public function income()
    {
        // Get income statistics and reports
        $totalIncome = $this->paymentModel->getTotalIncomeByLandlord($_SESSION['user_id']);
        $paymentStats = $this->paymentModel->getPaymentStatsByLandlord($_SESSION['user_id']);
        $payments = $this->paymentModel->getPaymentsByLandlord($_SESSION['user_id']);
        $maintenanceStats = $this->maintenanceModel->getMaintenanceStats($_SESSION['user_id']);

        // Calculate monthly income
        $monthlyIncome = [];
        if ($paymentStats) {
            foreach ($paymentStats as $stat) {
                $key = $stat->year . '-' . str_pad($stat->month, 2, '0', STR_PAD_LEFT);
                $monthlyIncome[$key] = $stat->completed_amount;
            }
        }

        $data = [
            'title' => 'Income Reports',
            'page' => 'income',
            'user_name' => $_SESSION['user_name'],
            'totalIncome' => $totalIncome,
            'paymentStats' => $paymentStats,
            'payments' => $payments,
            'maintenanceStats' => $maintenanceStats,
            'monthlyIncome' => $monthlyIncome,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('landlord/v_income', $data);
    }

    // Mark notification as read
    public function markNotificationRead($id)
    {
        if ($this->notificationModel->markAsRead($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    // Mark all notifications as read
    public function markAllNotificationsRead()
    {
        if ($this->notificationModel->markAllAsRead($_SESSION['user_id'])) {
            flash('notification_message', 'All notifications marked as read', 'alert alert-success');
        } else {
            flash('notification_message', 'Failed to mark notifications as read', 'alert alert-danger');
        }

        redirect('landlord/notifications');
    }

    // Delete notification
    public function deleteNotification($id)
    {
        if ($this->notificationModel->deleteNotification($id)) {
            flash('notification_message', 'Notification deleted', 'alert alert-success');
        } else {
            flash('notification_message', 'Failed to delete notification', 'alert alert-danger');
        }

        redirect('landlord/notifications');
    }

    // View inquiry/issue details
    public function issueDetails($id = null)
    {
        if (!$id) {
            flash('issue_error', 'Issue not found', 'alert alert-danger');
            redirect('landlord/inquiries');
        }

        $issueModel = $this->model('M_Issue');
        $issue = $issueModel->getIssueById($id);

        if (!$issue) {
            flash('issue_error', 'Issue not found', 'alert alert-danger');
            redirect('landlord/inquiries');
        }

        // Verify this landlord owns the property
        if ($issue->landlord_id != $_SESSION['user_id']) {
            flash('issue_error', 'Unauthorized access', 'alert alert-danger');
            redirect('landlord/inquiries');
        }

        $data = [
            'title' => 'Inquiry Details',
            'page' => 'inquiries',
            'user_name' => $_SESSION['user_name'],
            'issue' => $issue
        ];

        $this->view('landlord/v_issue_details', $data);
    }
}
