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
    private $messageModel;
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
        $this->messageModel = $this->model('M_Messages');
        $this->notificationModel = $this->model('M_Notifications');
        $this->reviewModel = $this->model('M_Reviews');
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        // Get dashboard statistics
        $propertyStats = $this->propertyModel->getPropertyStatsByLandlord($_SESSION['user_id']);
        $bookingStats = $this->bookingModel->getBookingStats($_SESSION['user_id'], 'landlord');
        $pendingBookings = $this->bookingModel->getPendingBookingsCount($_SESSION['user_id']);
        $totalIncome = $this->paymentModel->getTotalIncomeByLandlord($_SESSION['user_id']);
        $activeLeases = $this->leaseModel->getActiveLeasesCount($_SESSION['user_id']);
        $pendingMaintenance = $this->maintenanceModel->getPendingMaintenanceCount($_SESSION['user_id']);
        $recentBookings = $this->bookingModel->getBookingsByLandlord($_SESSION['user_id']);
        $recentPayments = $this->paymentModel->getRecentPayments($_SESSION['user_id'], 'landlord', 5);
        $unreadMessages = $this->messageModel->getUnreadCount($_SESSION['user_id']);
        $unreadNotifications = $this->notificationModel->getUnreadCount($_SESSION['user_id']);

        // Limit recent bookings to 5
        if (count($recentBookings) > 5) {
            $recentBookings = array_slice($recentBookings, 0, 5);
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
            'unreadMessages' => $unreadMessages,
            'unreadNotifications' => $unreadNotifications
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
            'bookingStats' => $bookingStats
        ];
        $this->view('landlord/v_bookings', $data);
    }

    public function inquiries()
    {
        // Get all messages for the landlord
        $messages = $this->messageModel->getMessagesByUser($_SESSION['user_id'], 'received');
        $unreadCount = $this->messageModel->getUnreadCount($_SESSION['user_id']);

        $data = [
            'title' => 'Tenant Inquiries',
            'page' => 'inquiries',
            'user_name' => $_SESSION['user_name'],
            'messages' => $messages,
            'unreadCount' => $unreadCount
        ];
        $this->view('landlord/v_inquiries', $data);
    }

    public function payment_history()
    {
        // Get all payments for landlord
        $payments = $this->paymentModel->getPaymentsByLandlord($_SESSION['user_id']);
        $totalIncome = $this->paymentModel->getTotalIncomeByLandlord($_SESSION['user_id']);
        $paymentStats = $this->paymentModel->getPaymentStatsByLandlord($_SESSION['user_id']);

        $data = [
            'title' => 'Payment History',
            'page' => 'payment_history',
            'user_name' => $_SESSION['user_name'],
            'payments' => $payments,
            'totalIncome' => $totalIncome,
            'paymentStats' => $paymentStats
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
            'reviewsAboutMe' => $reviewsAboutMe
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
            'unreadCount' => $unreadCount
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
            'user' => $user
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
            'monthlyIncome' => $monthlyIncome
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
}
