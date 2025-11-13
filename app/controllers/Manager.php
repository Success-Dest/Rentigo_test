<?php
require_once '../app/helpers/helper.php';

class Manager extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('M_Users');
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'property_manager') {
            redirect('users/login');
        }
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

        // Get manager's data
        $manager_id = $_SESSION['user_id'];
        $properties = $propertyModel->getAssignedProperties($manager_id);

        // Get recent maintenance requests
        $allMaintenance = $maintenanceModel->getAllMaintenanceRequests();
        $recentMaintenance = array_slice($allMaintenance, 0, 5);

        // Get recent payments
        $allPayments = $paymentModel->getAllPayments();
        $recentPayments = array_slice($allPayments, 0, 10);

        // Calculate statistics
        $totalProperties = count($properties);
        $totalUnits = 0;
        $occupiedUnits = 0;
        foreach ($properties as $property) {
            $totalUnits += $property->occupancy_total ?? 0;
            $occupiedUnits += $property->occupancy_occupied ?? 0;
        }

        // Calculate total income from payments
        $totalIncome = 0;
        $totalExpenses = 0;
        foreach ($allPayments as $payment) {
            if ($payment->status === 'completed') {
                $totalIncome += $payment->amount;
            }
        }
        foreach ($allMaintenance as $maintenance) {
            $totalExpenses += $maintenance->actual_cost ?? $maintenance->estimated_cost ?? 0;
        }

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
            'recentMaintenance' => $recentMaintenance
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
        $userModel = $this->model('M_Users');

        // Get all bookings to get tenant information
        $allBookings = $bookingModel->getAllBookings();

        // Separate by status
        $activeBookings = array_filter($allBookings, fn($b) => $b->status === 'active');
        $pendingBookings = array_filter($allBookings, fn($b) => $b->status === 'pending');
        $vacatedBookings = array_filter($allBookings, fn($b) => $b->status === 'completed' || $b->status === 'cancelled');

        $data = [
            'title' => 'Tenant Management',
            'page' => 'tenants',
            'user_name' => $_SESSION['user_name'],
            'activeBookings' => $activeBookings,
            'pendingBookings' => $pendingBookings,
            'vacatedBookings' => $vacatedBookings,
            'activeCount' => count($activeBookings),
            'pendingCount' => count($pendingBookings),
            'vacatedCount' => count($vacatedBookings)
        ];
        $this->view('manager/v_tenants', $data);
    }

    public function maintenance()
    {
        // Load maintenance model
        $maintenanceModel = $this->model('M_Maintenance');

        // Get all maintenance requests
        $allRequests = $maintenanceModel->getAllMaintenanceRequests();

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
            'allRequests' => $allRequests,
            'requestedRequests' => $requestedRequests,
            'quotedRequests' => $quotedRequests,
            'approvedRequests' => $approvedRequests,
            'completedRequests' => $completedRequests,
            'pendingApprovals' => $pendingApprovals
        ];
        $this->view('manager/v_maintenance', $data);
    }

    public function inspections()
    {
        redirect('inspections/index'); // route to inspection controller
    }

    public function issues()
    {
        $issueModel = $this->model('Issue');
        $allIssues = $issueModel->getAllIssues();

        $openIssues = array_filter($allIssues, fn($issue) => $issue->status === 'pending');
        $assignedIssues = array_filter($allIssues, fn($issue) => $issue->status === 'assigned');
        $inProgressIssues = array_filter($allIssues, fn($issue) => $issue->status === 'in_progress');
        $resolvedIssues = array_filter($allIssues, fn($issue) => $issue->status === 'resolved');

        $data = [
            'title' => 'Issue Tracking',
            'page' => 'issues',
            'user_name' => $_SESSION['user_name'],
            'allIssues' => $allIssues,
            'openIssues' => $openIssues,
            'assignedIssues' => $assignedIssues,
            'inProgressIssues' => $inProgressIssues,
            'resolvedIssues' => $resolvedIssues
        ];

        $this->view('manager/v_issues', $data);
    }

    public function leases()
    {
        // Load lease agreement model
        $leaseModel = $this->model('M_LeaseAgreements');

        // Get all lease agreements
        $allLeases = $leaseModel->getAllLeaseAgreements();

        // Filter by status
        $pendingLeases = array_filter($allLeases, fn($l) => $l->validation_status === 'pending_review' || $l->validation_status === 'pending');
        $validatedLeases = array_filter($allLeases, fn($l) => $l->validation_status === 'validated' || $l->validation_status === 'approved');
        $rejectedLeases = array_filter($allLeases, fn($l) => $l->validation_status === 'rejected');

        $data = [
            'title' => 'Lease Agreements',
            'page' => 'leases',
            'user_name' => $_SESSION['user_name'],
            'allLeases' => $allLeases,
            'pendingLeases' => $pendingLeases,
            'validatedLeases' => $validatedLeases,
            'rejectedLeases' => $rejectedLeases
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
            'providers' => $allProviders
        ];
        $this->view('manager/v_providers', $data);
    }
}
