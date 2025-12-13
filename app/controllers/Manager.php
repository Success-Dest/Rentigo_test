<?php
require_once '../app/helpers/helper.php';

class Manager extends Controller
{
    private $userModel;
    private $notificationModel;

    public function __construct()
    {
        $this->userModel = $this->model('M_Users');
        $this->notificationModel = $this->model('M_Notifications');
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'property_manager') {
            redirect('users/login');
        }
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
        // Load models
        $propertyModel = $this->model('M_ManagerProperties');
        $maintenanceModel = $this->model('M_Maintenance');
        $paymentModel = $this->model('M_Payments');
        $maintenanceQuotationModel = $this->model('M_MaintenanceQuotations');

        // Get manager's data
        $manager_id = $_SESSION['user_id'];
        $properties = $propertyModel->getAssignedProperties($manager_id);

        // Get recent maintenance requests
        $allMaintenance = $maintenanceModel->getAllMaintenanceRequests();
        $recentMaintenance = array_slice($allMaintenance, 0, 5);

        // Get recent rental payments
        $allPayments = $paymentModel->getAllPayments();

        // Get recent maintenance payments
        $maintenancePayments = $maintenanceQuotationModel->getAllMaintenancePayments();

        // Combine and sort all payments by date
        $combinedPayments = array_merge($allPayments, $maintenancePayments);
        usort($combinedPayments, function ($a, $b) {
            $dateA = $a->payment_date ?? $a->due_date ?? $a->created_at;
            $dateB = $b->payment_date ?? $b->due_date ?? $b->created_at;
            return strtotime($dateB) - strtotime($dateA);
        });
        $recentPayments = array_slice($combinedPayments, 0, 10);

        // Calculate statistics
        $totalProperties = count($properties);
        $totalUnits = 0;
        $occupiedUnits = 0;
        foreach ($properties as $property) {
            $totalUnits += $property->occupancy_total ?? 0;
            $occupiedUnits += $property->occupancy_occupied ?? 0;
        }

        // Calculate total income from payments (10% platform service fee) + maintenance payments (100%)
        $totalIncome = 0;
        $totalExpenses = 0;

        // Rental payment income (10% service fee)
        foreach ($allPayments as $payment) {
            if ($payment->status === 'completed') {
                // Platform earns 10% service fee from each rental payment
                $totalIncome += ($payment->amount * 0.10);
            }
        }

        // Maintenance payment income (100% - full payment amount)
        $maintenanceIncome = $maintenanceQuotationModel->getTotalMaintenanceIncome();
        $totalIncome += $maintenanceIncome;

        foreach ($allMaintenance as $maintenance) {
            $totalExpenses += $maintenance->actual_cost ?? $maintenance->estimated_cost ?? 0;
        }

        // Get issue statistics
        $issueModel = $this->model('M_Issue');
        $issueStats = $issueModel->getIssueStats($manager_id, 'manager');

        $data = [
            'title' => 'Property Manager Dashboard',
            'page' => 'dashboard',
            'user_name' => $_SESSION['user_name'],
            'totalProperties' => $totalProperties,
            'totalUnits' => $totalUnits,
            'occupiedUnits' => $occupiedUnits,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'recentPayments' => $recentPayments,
            'recentMaintenance' => $recentMaintenance,
            'issueStats' => $issueStats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('manager/v_dashboard', $data);
    }

    public function properties()
    {
        redirect('ManagerProperties/index');
    }

    public function tenants()
    {
        // Load models
        $bookingModel = $this->model('M_Bookings');
        $propertyModel = $this->model('M_ManagerProperties');
        $manager_id = $_SESSION['user_id'];

        // Get properties assigned to this manager
        $assignedProperties = $propertyModel->getAssignedProperties($manager_id);
        $propertyIds = array_map(fn($p) => $p->id, $assignedProperties ?? []);

        // Get bookings only for assigned properties
        $allBookings = [];
        if (!empty($propertyIds)) {
            $allBookings = $bookingModel->getBookingsByProperties($propertyIds);
        }

        // Separate by status
        $activeBookings = array_filter($allBookings, fn($b) => $b->status === 'active' || $b->status === 'approved');
        $pendingBookings = array_filter($allBookings, fn($b) => $b->status === 'pending');
        $vacatedBookings = array_filter($allBookings, fn($b) => $b->status === 'completed' || $b->status === 'cancelled');

        $data = [
            'title' => 'Tenant Management',
            'page' => 'tenants',
            'user_name' => $_SESSION['user_name'],
            'assignedPropertiesCount' => count($assignedProperties ?? []),
            'activeBookings' => $activeBookings,
            'pendingBookings' => $pendingBookings,
            'vacatedBookings' => $vacatedBookings,
            'activeCount' => count($activeBookings),
            'pendingCount' => count($pendingBookings),
            'vacatedCount' => count($vacatedBookings),
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('manager/v_tenants', $data);
    }

    public function maintenance()
    {
        // Load maintenance model
        $maintenanceModel = $this->model('M_Maintenance');
        $manager_id = $_SESSION['user_id'];

        // Get maintenance requests for this manager's properties only
        $allRequests = $maintenanceModel->getMaintenanceByManager($manager_id);

        // Get maintenance statistics
        $maintenanceStats = $maintenanceModel->getMaintenanceStats(null, $manager_id);

        // Filter by status
        $requestedRequests = array_filter($allRequests, fn($r) => $r->status === 'requested');
        $quotedRequests = array_filter($allRequests, fn($r) => $r->status === 'quoted');
        $approvedRequests = array_filter($allRequests, fn($r) => $r->status === 'approved' || $r->status === 'in_progress');
        $completedRequests = array_filter($allRequests, fn($r) => $r->status === 'completed');

        // Get pending quotation approvals (quoted status)
        $pendingApprovals = $quotedRequests;

        $data = [
            'title' => 'Maintenance Management',
            'page' => 'maintenance',
            'user_name' => $_SESSION['user_name'],
            'maintenanceRequests' => $allRequests,  // View expects this
            'maintenanceStats' => $maintenanceStats,  // View expects this
            'allRequests' => $allRequests,
            'requestedRequests' => $requestedRequests,
            'quotedRequests' => $quotedRequests,
            'approvedRequests' => $approvedRequests,
            'completedRequests' => $completedRequests,
            'pendingApprovals' => $pendingApprovals,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('manager/v_maintenance', $data);
    }

    public function inspections()
    {
        redirect('inspections/index'); // route to inspection controller
    }

    public function issues()
    {
        $issueModel = $this->model('M_Issue');
        $manager_id = $_SESSION['user_id'];

        // Get issues for properties assigned to this PM
        $allIssues = $issueModel->getIssuesByManager($manager_id);

        $openIssues = array_filter($allIssues, fn($issue) => $issue->status === 'pending');
        $assignedIssues = array_filter($allIssues, fn($issue) => $issue->status === 'assigned');
        $inProgressIssues = array_filter($allIssues, fn($issue) => $issue->status === 'in_progress');
        $resolvedIssues = array_filter($allIssues, fn($issue) => $issue->status === 'resolved');

        // Get issue statistics
        $stats = $issueModel->getIssueStats($manager_id, 'manager');

        $data = [
            'title' => 'Issue Tracking',
            'page' => 'issues',
            'user_name' => $_SESSION['user_name'],
            'allIssues' => $allIssues,
            'openIssues' => $openIssues,
            'assignedIssues' => $assignedIssues,
            'inProgressIssues' => $inProgressIssues,
            'resolvedIssues' => $resolvedIssues,
            'issueStats' => $stats,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('manager/v_issues', $data);
    }

    // Issue details and management page
    public function issueDetails($id = null)
    {
        if (!$id) {
            flash('issue_error', 'Issue not found', 'alert alert-danger');
            redirect('manager/issues');
        }

        $issueModel = $this->model('M_Issue');
        $issue = $issueModel->getIssueById($id);

        if (!$issue) {
            flash('issue_error', 'Issue not found', 'alert alert-danger');
            redirect('manager/issues');
        }

        // Verify this PM manages this property
        $propertyModel = $this->model('M_ManagerProperties');
        $manager = $propertyModel->getManagerByProperty($issue->property_id);

        if (!$manager || $manager->manager_id != $_SESSION['user_id']) {
            flash('issue_error', 'Unauthorized access', 'alert alert-danger');
            redirect('manager/issues');
        }

        $data = [
            'title' => 'Issue Details',
            'page' => 'issues',
            'user_name' => $_SESSION['user_name'],
            'issue' => $issue,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('manager/v_issue_details', $data);
    }

    // Update issue status
    public function updateIssueStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('manager/issues');
        }

        header('Content-Type: application/json');

        $issue_id = $_POST['issue_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $resolution_notes = $_POST['resolution_notes'] ?? null;

        if (!$issue_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        $issueModel = $this->model('M_Issue');
        $issue = $issueModel->getIssueById($issue_id);

        if (!$issue) {
            echo json_encode(['success' => false, 'message' => 'Issue not found']);
            exit();
        }

        // Verify this PM manages this property
        $propertyModel = $this->model('M_ManagerProperties');
        $manager = $propertyModel->getManagerByProperty($issue->property_id);

        if (!$manager || $manager->manager_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        }

        if ($issueModel->updateStatus($issue_id, $status, $resolution_notes)) {
            // Send notification to tenant
            $notificationModel = $this->model('M_Notifications');
            $statusText = ucfirst(str_replace('_', ' ', $status));
            
            // Get current date/time
            $updateDateTime = date('F j, Y \a\t g:i A');
            
            // Get Property Manager name
            $pmName = $_SESSION['user_name'] ?? 'Property Manager';
            
            // Create enhanced notification message
            $notificationMessage = sprintf(
                'Your issue "%s" has been updated to: %s by %s on %s',
                $issue->title,
                $statusText,
                $pmName,
                $updateDateTime
            );

            $notificationModel->createNotification([
                'user_id' => $issue->tenant_id,
                'type' => 'issue_update',
                'title' => 'Issue Status Updated',
                'message' => $notificationMessage,
                'link' => 'issues/track'
            ]);

            echo json_encode(['success' => true, 'message' => 'Issue status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update issue status']);
        }
        exit();
    }

    // Notify landlord about issue
    public function notifyLandlordAboutIssue()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('manager/issues');
        }

        header('Content-Type: application/json');

        $issue_id = $_POST['issue_id'] ?? null;
        $message = $_POST['message'] ?? '';

        if (!$issue_id) {
            echo json_encode(['success' => false, 'message' => 'Issue ID is required']);
            exit();
        }

        $issueModel = $this->model('M_Issue');
        $issue = $issueModel->getIssueById($issue_id);

        if (!$issue) {
            echo json_encode(['success' => false, 'message' => 'Issue not found']);
            exit();
        }

        // Verify this PM manages this property
        $propertyModel = $this->model('M_ManagerProperties');
        $manager = $propertyModel->getManagerByProperty($issue->property_id);

        if (!$manager || $manager->manager_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        }

        // Send notification to landlord
        $notificationModel = $this->model('M_Notifications');

        $notificationMessage = $message ?: 'Your property manager has flagged a tenant issue that may require a maintenance request: "' . $issue->title . '". Please review and take appropriate action.';

        $notificationModel->createNotification([
            'user_id' => $issue->landlord_id,
            'type' => 'issue_landlord_action',
            'title' => 'Tenant Issue Requires Your Attention',
            'message' => $notificationMessage,
            'link' => 'landlord/issueDetails/' . $issue_id
        ]);

        // Mark landlord as notified
        $issueModel->markLandlordNotified($issue_id);

        echo json_encode(['success' => true, 'message' => 'Landlord has been notified']);
        exit();
    }

    public function leases()
    {
        // Load lease agreement model
        $leaseModel = $this->model('M_LeaseAgreements');

        // Get manager's assigned property leases
        $manager_id = $_SESSION['user_id'];
        $allLeases = $leaseModel->getLeasesByManager($manager_id);

        // Filter by status
        $draftLeases = array_filter($allLeases, fn($l) => $l->status === 'draft');
        $activeLeases = array_filter($allLeases, fn($l) => $l->status === 'active');
        $completedLeases = array_filter($allLeases, fn($l) => $l->status === 'completed');

        $data = [
            'title' => 'Lease Agreements',
            'page' => 'leases',
            'user_name' => $_SESSION['user_name'],
            'allLeases' => $allLeases,
            'draftLeases' => $draftLeases,
            'activeLeases' => $activeLeases,
            'completedLeases' => $completedLeases,
            'draftCount' => count($draftLeases),
            'activeCount' => count($activeLeases),
            'completedCount' => count($completedLeases),
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('manager/v_leases', $data);
    }

    public function providers()
    {
        // Load service provider model
        $providerModel = $this->model('M_ServiceProviders');

        // Get all service providers
        $allProviders = $providerModel->getAllProviders();

        $data = [
            'title' => 'Service Providers',
            'page' => 'providers',
            'user_name' => $_SESSION['user_name'],
            'providers' => $allProviders,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('manager/v_providers', $data);
    }

    public function bookings()
    {
        // Load booking model
        $bookingModel = $this->model('M_Bookings');
        $propertyModel = $this->model('M_ManagerProperties');

        // Get manager's assigned properties
        $manager_id = $_SESSION['user_id'];
        $assignedProperties = $propertyModel->getAssignedProperties($manager_id);

        // Get property IDs
        $propertyIds = array_map(fn($p) => $p->id, $assignedProperties ?? []);

        // Get all bookings for assigned properties
        $allBookings = [];
        if (!empty($propertyIds)) {
            $allBookings = $bookingModel->getBookingsByProperties($propertyIds);
        }

        // Get filter parameters from GET request
        $statusFilter = $_GET['status'] ?? 'all';
        $propertyFilter = $_GET['property_id'] ?? 'all';
        $dateFromFilter = $_GET['date_from'] ?? '';
        $dateToFilter = $_GET['date_to'] ?? '';

        // Calculate stats for all bookings (for stats cards - always show totals)
        $pendingBookings = array_filter($allBookings, fn($b) => $b->status === 'pending');
        $approvedBookings = array_filter($allBookings, fn($b) => $b->status === 'approved');
        $rejectedBookings = array_filter($allBookings, fn($b) => $b->status === 'rejected');

        // Apply filters to bookings
        $filteredBookings = $allBookings;

        // Filter by status
        if ($statusFilter !== 'all') {
            $filteredBookings = array_filter($filteredBookings, fn($b) => $b->status === $statusFilter);
        }

        // Filter by property
        if ($propertyFilter !== 'all' && is_numeric($propertyFilter)) {
            $filteredBookings = array_filter($filteredBookings, fn($b) => $b->property_id == $propertyFilter);
        }

        // Filter by date range (move-in date)
        if (!empty($dateFromFilter)) {
            $filteredBookings = array_filter($filteredBookings, function($b) use ($dateFromFilter) {
                return strtotime($b->move_in_date) >= strtotime($dateFromFilter);
            });
        }

        if (!empty($dateToFilter)) {
            $filteredBookings = array_filter($filteredBookings, function($b) use ($dateToFilter) {
                return strtotime($b->move_in_date) <= strtotime($dateToFilter);
            });
        }

        // Re-index array after filtering
        $filteredBookings = array_values($filteredBookings);

        $data = [
            'title' => 'Booking Management',
            'page' => 'bookings',
            'user_name' => $_SESSION['user_name'],
            'allBookings' => $filteredBookings, // Filtered bookings for display
            'assignedProperties' => $assignedProperties, // For property filter dropdown
            'pendingCount' => count($pendingBookings), // Total stats
            'approvedCount' => count($approvedBookings), // Total stats
            'rejectedCount' => count($rejectedBookings), // Total stats
            // Current filter values (for maintaining form state)
            'currentStatusFilter' => $statusFilter,
            'currentPropertyFilter' => $propertyFilter,
            'currentDateFromFilter' => $dateFromFilter,
            'currentDateToFilter' => $dateToFilter,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];
        $this->view('manager/v_bookings', $data);
    }

    public function notifications()
    {
        // Get all notifications for the property manager
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
        $this->view('manager/v_notifications', $data);
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

        redirect('manager/notifications');
    }

    // Delete notification
    public function deleteNotification($id)
    {
        if ($this->notificationModel->deleteNotification($id)) {
            flash('notification_message', 'Notification deleted', 'alert alert-success');
        } else {
            flash('notification_message', 'Failed to delete notification', 'alert alert-danger');
        }

        redirect('manager/notifications');
    }

    // Payment tracking for assigned properties
    public function payments()
    {
        $paymentModel = $this->model('M_Payments');
        $propertyModel = $this->model('M_ManagerProperties');

        $manager_id = $_SESSION['user_id'];

        // Get assigned properties
        $assignedProperties = $propertyModel->getAssignedProperties($manager_id);
        $propertyIds = array_map(fn($p) => $p->id, $assignedProperties ?? []);

        // Get all payments for assigned properties
        $allPayments = [];
        if (!empty($propertyIds)) {
            foreach ($propertyIds as $propertyId) {
                $propertyPayments = $paymentModel->getPaymentsByProperty($propertyId);
                $allPayments = array_merge($allPayments, $propertyPayments);
            }
        }

        // Sort payments by date (newest first)
        usort($allPayments, function ($a, $b) {
            $dateA = $a->payment_date ?? $a->due_date;
            $dateB = $b->payment_date ?? $b->due_date;
            return strtotime($dateB) - strtotime($dateA);
        });

        // Calculate statistics
        $totalIncome = 0;
        $completedCount = 0;
        $pendingCount = 0;
        $pendingAmount = 0;

        foreach ($allPayments as $payment) {
            if ($payment->status === 'completed') {
                $totalIncome += $payment->amount;
                $completedCount++;
            } else if ($payment->status === 'pending') {
                $pendingCount++;
                $pendingAmount += $payment->amount;
            }
        }

        $data = [
            'title' => 'Payment Tracking',
            'page' => 'payments',
            'user_name' => $_SESSION['user_name'],
            'payments' => $allPayments,
            'totalIncome' => $totalIncome,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'pendingAmount' => $pendingAmount,
            'propertyCount' => count($assignedProperties),
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('manager/v_payments', $data);
    }

    // View all payments (rental + maintenance)
    public function allPayments()
    {
        $paymentModel = $this->model('M_Payments');
        $maintenanceQuotationModel = $this->model('M_MaintenanceQuotations');

        // Get all rental payments
        $allPayments = $paymentModel->getAllPayments();

        // Get all maintenance payments
        $maintenancePayments = $maintenanceQuotationModel->getAllMaintenancePayments();

        // Combine and sort all payments by date
        $combinedPayments = array_merge($allPayments, $maintenancePayments);
        usort($combinedPayments, function ($a, $b) {
            $dateA = $a->payment_date ?? $a->due_date ?? $a->created_at;
            $dateB = $b->payment_date ?? $b->due_date ?? $b->created_at;
            return strtotime($dateB) - strtotime($dateA);
        });

        // Calculate statistics
        $totalIncome = 0;
        $completedCount = 0;
        $pendingCount = 0;
        $pendingAmount = 0;

        foreach ($combinedPayments as $payment) {
            $isMaintenance = isset($payment->payment_type) && $payment->payment_type === 'maintenance';
            $platformFee = $isMaintenance ? $payment->amount : ($payment->amount * 0.10);

            if ($payment->status === 'completed') {
                $totalIncome += $platformFee;
                $completedCount++;
            } else if ($payment->status === 'pending') {
                $pendingCount++;
                $pendingAmount += $platformFee;
            }
        }

        $data = [
            'title' => 'All Payments',
            'page' => 'payments',
            'user_name' => $_SESSION['user_name'],
            'allPayments' => $combinedPayments,
            'totalIncome' => $totalIncome,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'pendingAmount' => $pendingAmount,
            'unread_notifications' => $this->getUnreadNotificationCount()
        ];

        $this->view('manager/v_all_payments', $data);
    }
}
