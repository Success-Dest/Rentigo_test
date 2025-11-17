<?php
require_once '../app/helpers/helper.php';

class Tenant extends Controller
{
    private $bookingModel;
    private $paymentModel;
    private $leaseModel;
    private $reviewModel;
    private $notificationModel;
    private $issueModel;

    public function __construct()
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'tenant') {
            redirect('users/login');
        }

        $this->bookingModel = $this->model('M_Bookings');
        $this->paymentModel = $this->model('M_Payments');
        $this->leaseModel = $this->model('M_LeaseAgreements');
        $this->reviewModel = $this->model('M_Reviews');
        $this->notificationModel = $this->model('M_Notifications');
        $this->issueModel = $this->model('Issue');
    }

    // Helper method to get unread notification count
    private function getUnreadNotificationCount()
    {
        return $this->notificationModel->getUnreadCount($_SESSION['user_id']);
    }

    // Main dashboard page
    public function index()
    {
        // Get dashboard data
        $activeBooking = $this->bookingModel->getActiveBookingByTenant($_SESSION['user_id']);
        $activeLease = $this->leaseModel->getActiveLeaseByTenant($_SESSION['user_id']);
        $pendingPayments = $this->paymentModel->getPendingPaymentsByTenant($_SESSION['user_id']);
        $recentIssues = $this->issueModel->getRecentIssues($_SESSION['user_id'], 5);
        $bookingStats = $this->bookingModel->getBookingStats($_SESSION['user_id'], 'tenant');
        $unreadNotifications = $this->notificationModel->getUnreadCount($_SESSION['user_id']);

        $data = [
            'title' => 'Tenant Dashboard - TenantHub',
            'page' => 'dashboard',
            'user_name' => $_SESSION['user_name'],
            'activeBooking' => $activeBooking,
            'activeLease' => $activeLease,
            'pendingPayments' => $pendingPayments,
            'recentIssues' => $recentIssues,
            'bookingStats' => $bookingStats,
            'unreadNotifications' => $unreadNotifications,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_dashboard', $data);
    }

    public function search_properties()
    {
        redirect('tenantproperties/index');
    }

    public function bookings()
    {
        // Get all bookings for the tenant
        $bookings = $this->bookingModel->getBookingsByTenant($_SESSION['user_id']);
        $bookingStats = $this->bookingModel->getBookingStats($_SESSION['user_id'], 'tenant');

        $data = [
            'title' => 'My Bookings - TenantHub',
            'page' => 'bookings',
            'user_name' => $_SESSION['user_name'],
            'bookings' => $bookings,
            'bookingStats' => $bookingStats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_bookings', $data);
    }

    public function pay_rent()
    {
        // Get pending payments for the tenant
        $pendingPayments = $this->paymentModel->getPendingPaymentsByTenant($_SESSION['user_id']);
        $paymentHistory = $this->paymentModel->getPaymentsByTenant($_SESSION['user_id']);
        $totalPayments = $this->paymentModel->getTotalPaymentsByTenant($_SESSION['user_id']);
        $overduePayments = $this->paymentModel->getOverduePayments($_SESSION['user_id']);

        $data = [
            'title' => 'Pay Rent - TenantHub',
            'page' => 'pay_rent',
            'user_name' => $_SESSION['user_name'],
            'pendingPayments' => $pendingPayments,
            'paymentHistory' => $paymentHistory,
            'totalPayments' => $totalPayments,
            'overduePayments' => $overduePayments,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_pay_rent', $data);
    }

    public function agreements()
    {
        // Get all lease agreements for the tenant
        $leases = $this->leaseModel->getLeasesByTenant($_SESSION['user_id']);
        $activeLease = $this->leaseModel->getActiveLeaseByTenant($_SESSION['user_id']);
        $leaseStats = $this->leaseModel->getLeaseStats($_SESSION['user_id'], 'tenant');

        $data = [
            'title' => 'Lease Agreements - TenantHub',
            'page' => 'agreements',
            'user_name' => $_SESSION['user_name'],
            'leases' => $leases,
            'activeLease' => $activeLease,
            'leaseStats' => $leaseStats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_agreements', $data);
    }

    public function report_issue()
    {
        redirect('issues/report');
    }

    public function track_issues()
    {
        redirect('issues/track');
    }

    public function my_reviews()
    {
        // Get all reviews by the tenant
        $myReviews = $this->reviewModel->getReviewsByReviewer($_SESSION['user_id']);
        $reviewableBookings = $this->reviewModel->getReviewableBookings($_SESSION['user_id']);

        $data = [
            'title' => 'My Reviews - TenantHub',
            'page' => 'my_reviews',
            'user_name' => $_SESSION['user_name'],
            'myReviews' => $myReviews,
            'reviewableBookings' => $reviewableBookings,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_my_reviews', $data);
    }

    public function notifications()
    {
        // Get all notifications for the tenant
        $notifications = $this->notificationModel->getNotificationsByUser($_SESSION['user_id']);
        $unreadCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);

        $data = [
            'title' => 'Notifications - TenantHub',
            'page' => 'notifications',
            'user_name' => $_SESSION['user_name'],
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_notifications', $data);
    }

    public function feedback()
    {
        // Get reviews about the tenant (from landlords)
        $reviewsAboutMe = $this->reviewModel->getReviewsAboutUser($_SESSION['user_id'], 'tenant');

        $data = [
            'title' => 'Landlord Reviews - TenantHub',
            'page' => 'feedback',
            'user_name' => $_SESSION['user_name'],
            'reviewsAboutMe' => $reviewsAboutMe,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_feedback', $data);
    }

    public function settings()
    {
        // User settings page
        $userModel = $this->model('M_Users');
        $user = $userModel->getUserById($_SESSION['user_id']);

        $data = [
            'title' => 'Settings - TenantHub',
            'page' => 'settings',
            'user_name' => $_SESSION['user_name'],
            'user' => $user,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('tenant/v_settings', $data);
    }

    // Mark notification as read (AJAX endpoint)
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

        redirect('tenant/notifications');
    }

    // Delete notification
    public function deleteNotification($id)
    {
        if ($this->notificationModel->deleteNotification($id)) {
            flash('notification_message', 'Notification deleted', 'alert alert-success');
        } else {
            flash('notification_message', 'Failed to delete notification', 'alert alert-danger');
        }

        redirect('tenant/notifications');
    }
}
