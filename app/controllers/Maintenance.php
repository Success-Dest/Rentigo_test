<?php
require_once '../app/helpers/helper.php';

class Maintenance extends Controller
{
    private $maintenanceModel;
    private $propertyModel;
    private $providerModel;
    private $notificationModel;

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->maintenanceModel = $this->model('M_Maintenance');
        $this->propertyModel = $this->model('M_Properties');
        $this->providerModel = $this->model('M_ServiceProviders');
        $this->notificationModel = $this->model('M_Notifications');
    }

    public function index()
    {
        $user_type = $_SESSION['user_type'];

        if ($user_type == 'landlord') {
            $maintenanceRequests = $this->maintenanceModel->getMaintenanceByLandlord($_SESSION['user_id']);
            $maintenanceStats = $this->maintenanceModel->getMaintenanceStats($_SESSION['user_id']);
        } else if ($user_type == 'property_manager') {
            $maintenanceRequests = $this->maintenanceModel->getMaintenanceByManager($_SESSION['user_id']);
            $maintenanceStats = $this->maintenanceModel->getMaintenanceStats(null, $_SESSION['user_id']);
        } else {
            redirect('users/login');
        }

        $data = [
            'title' => 'Maintenance Requests',
            'page' => 'maintenance',
            'user_name' => $_SESSION['user_name'],
            'maintenanceRequests' => $maintenanceRequests,
            'maintenanceStats' => $maintenanceStats
        ];

        if ($user_type == 'landlord') {
            $this->view('landlord/v_maintenance', $data);
        } else {
            $this->view('manager/v_maintenance', $data);
        }
    }

    // Show new maintenance request form
    public function create()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        // Get user's properties
        $properties = [];
        if ($_SESSION['user_type'] == 'landlord') {
            $properties = $this->propertyModel->getPropertiesByLandlord($_SESSION['user_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Validate and create maintenance request
            $data = [
                'property_id' => trim($_POST['property_id']),
                'landlord_id' => $_SESSION['user_id'],
                'issue_id' => trim($_POST['issue_id']) ?: null,
                'requester_id' => $_SESSION['user_id'],
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'category' => trim($_POST['category']),
                'priority' => trim($_POST['priority']),
                'status' => 'pending',
                'estimated_cost' => trim($_POST['estimated_cost']) ?: null,
                'notes' => trim($_POST['notes']) ?: ''
            ];

            // Validate required fields
            if (empty($data['property_id']) || empty($data['title']) || empty($data['description']) || empty($data['category'])) {
                flash('maintenance_message', 'Please fill in all required fields', 'alert alert-danger');
                redirect('maintenance/create');
            }

            // Create maintenance request
            if ($this->maintenanceModel->createMaintenanceRequest($data)) {
                flash('maintenance_message', 'Maintenance request created successfully', 'alert alert-success');
                redirect('maintenance/index');
            } else {
                flash('maintenance_message', 'Failed to create maintenance request', 'alert alert-danger');
                redirect('maintenance/create');
            }
        } else {
            $data = [
                'title' => 'New Maintenance Request - Rentigo',
                'page' => 'maintenance',
                'user_name' => $_SESSION['user_name'],
                'properties' => $properties
            ];

            $this->view('landlord/v_new_maintenance_request', $data);
        }
    }

    // View maintenance request details
    public function view($id)
    {
        $maintenance = $this->maintenanceModel->getMaintenanceById($id);

        if (!$maintenance) {
            flash('maintenance_message', 'Maintenance request not found', 'alert alert-danger');
            redirect('maintenance/index');
        }

        // Check if user has permission to view
        if ($_SESSION['user_type'] == 'landlord' && $maintenance->landlord_id != $_SESSION['user_id']) {
            flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
            redirect('maintenance/index');
        }

        $data = [
            'title' => 'Maintenance Details',
            'page' => 'maintenance',
            'user_name' => $_SESSION['user_name'],
            'maintenance' => $maintenance
        ];

        if ($_SESSION['user_type'] == 'landlord') {
            $this->view('landlord/v_maintenance_details', $data);
        } else {
            $this->view('manager/v_maintenance_details', $data);
        }
    }

    // Assign service provider to maintenance request
    public function assignProvider($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $provider_id = trim($_POST['provider_id']);
            $scheduled_date = trim($_POST['scheduled_date']);

            if ($this->maintenanceModel->assignProvider($id, $provider_id, $scheduled_date)) {
                flash('maintenance_message', 'Service provider assigned successfully', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Failed to assign service provider', 'alert alert-danger');
            }

            redirect('maintenance/view/' . $id);
        }
    }

    // Update maintenance status
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $status = trim($_POST['status']);

            if ($this->maintenanceModel->updateMaintenanceStatus($id, $status)) {
                flash('maintenance_message', 'Status updated successfully', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Failed to update status', 'alert alert-danger');
            }

            redirect('maintenance/view/' . $id);
        }
    }

    // Complete maintenance request
    public function complete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $actual_cost = trim($_POST['actual_cost']);
            $completion_notes = trim($_POST['completion_notes']);

            if ($this->maintenanceModel->completeMaintenance($id, $actual_cost, $completion_notes)) {
                flash('maintenance_message', 'Maintenance request completed successfully', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Failed to complete maintenance request', 'alert alert-danger');
            }

            redirect('maintenance/view/' . $id);
        }
    }

    // Cancel maintenance request
    public function cancel($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $cancellation_reason = trim($_POST['cancellation_reason']);

            if ($this->maintenanceModel->cancelMaintenance($id, $cancellation_reason)) {
                flash('maintenance_message', 'Maintenance request cancelled', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Failed to cancel maintenance request', 'alert alert-danger');
            }

            redirect('maintenance/index');
        }
    }

    // Delete maintenance request
    public function delete($id)
    {
        $maintenance = $this->maintenanceModel->getMaintenanceById($id);

        if (!$maintenance) {
            flash('maintenance_message', 'Maintenance request not found', 'alert alert-danger');
            redirect('maintenance/index');
        }

        // Check if user has permission to delete
        if ($_SESSION['user_type'] == 'landlord' && $maintenance->landlord_id != $_SESSION['user_id']) {
            flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
            redirect('maintenance/index');
        }

        if ($this->maintenanceModel->deleteMaintenance($id)) {
            flash('maintenance_message', 'Maintenance request deleted successfully', 'alert alert-success');
        } else {
            flash('maintenance_message', 'Failed to delete maintenance request', 'alert alert-danger');
        }

        redirect('maintenance/index');
    }
}
