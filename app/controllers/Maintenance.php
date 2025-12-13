<?php
require_once '../app/helpers/helper.php';

class Maintenance extends Controller
{
    private $maintenanceModel;
    private $propertyModel;
    private $providerModel;
    private $notificationModel;
    private $quotationModel;

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->maintenanceModel = $this->model('M_Maintenance');
        $this->propertyModel = $this->model('M_Properties');
        $this->providerModel = $this->model('M_ServiceProviders');
        $this->notificationModel = $this->model('M_Notifications');
        $this->quotationModel = $this->model('M_MaintenanceQuotations');
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
            $request_id = $this->maintenanceModel->createMaintenanceRequest($data);

            if ($request_id) {
                // Get property details to find the property manager
                $property = $this->propertyModel->getPropertyById($data['property_id']);

                // Send notification to property manager if assigned
                if ($property && $property->manager_id) {
                    $this->notificationModel->createNotification([
                        'user_id' => $property->manager_id,
                        'type' => 'maintenance_request',
                        'title' => 'New Maintenance Request',
                        'message' => 'New maintenance request: ' . $data['title'] . ' at ' . $property->address,
                        'link' => '/maintenance/details/' . $request_id
                    ]);
                }

                flash('maintenance_message', 'Maintenance request created successfully', 'alert alert-success');
                redirect('maintenance/index');
            } else {
                flash('maintenance_message', 'Failed to create maintenance request', 'alert alert-danger');
                redirect('maintenance/create');
            }
        } else {
            $data = [
                'page' => 'maintenance',
                'user_name' => $_SESSION['user_name'],
                'properties' => $properties,
                'property_id' => '',
                'property_err' => '',
                'title' => '',
                'title_err' => '',
                'category' => '',
                'category_err' => '',
                'priority' => '',
                'priority_err' => '',
                'description' => '',
                'description_err' => '',
                'notes' => '',
                'estimated_cost' => ''
            ];

            $this->view('landlord/v_new_maintenance_request', $data);
        }
    }

    // View maintenance request details
    public function details($id)
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

        // Get quotations for this request
        $quotations = $this->quotationModel->getQuotationsByRequest($id);

        // Get payment info if exists
        $payment = $this->quotationModel->getPaymentByRequest($id);

        // Get service providers for assignment
        $providers = $this->providerModel->getActiveProviders();

        $data = [
            'title' => 'Maintenance Details',
            'page' => 'maintenance',
            'user_name' => $_SESSION['user_name'],
            'maintenance' => $maintenance,
            'quotations' => $quotations,
            'payment' => $payment,
            'providers' => $providers
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
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $provider_id = trim($_POST['provider_id']);
            $scheduled_date_input = trim($_POST['scheduled_date']);
            $scheduled_date = $scheduled_date_input !== '' ? $scheduled_date_input : null;

            if ($this->maintenanceModel->assignProvider($id, $provider_id, $scheduled_date)) {
                flash('maintenance_message', 'Service provider assigned successfully', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Failed to assign service provider', 'alert alert-danger');
            }

            redirect('maintenance/details/' . $id);
        }
    }

       // Update maintenance status
       public function updateStatus($id)
       {
           if ($_SERVER['REQUEST_METHOD'] == 'POST') {
               $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
   
               $status = trim($_POST['status']);
   
               // Get maintenance request details
               $maintenance = $this->maintenanceModel->getMaintenanceById($id);
   
               if (!$maintenance) {
                   flash('maintenance_message', 'Maintenance request not found', 'alert alert-danger');
                   redirect('maintenance/index');
               }
   
               // Verify Property Manager has permission (if user is PM)
               if ($_SESSION['user_type'] === 'property_manager') {
                   $propertyModel = $this->model('M_Properties');
                   $property = $propertyModel->getPropertyById($maintenance->property_id);
                   
                   if (!$property || $property->manager_id != $_SESSION['user_id']) {
                       flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
                       redirect('maintenance/index');
                   }
               }
   
               if ($this->maintenanceModel->updateMaintenanceStatus($id, $status)) {
                   // Send notification to landlord if user is Property Manager
                   if ($_SESSION['user_type'] === 'property_manager' && $maintenance->landlord_id) {
                       $statusText = ucfirst(str_replace('_', ' ', $status));
                       
                       // Get current date/time
                      $updateDateTime = date('F j, Y \a\t g:i A');
                       
                       // Get Property Manager name
                       $pmName = $_SESSION['user_name'] ?? 'Property Manager';
                       
                       // Create notification message
                       $notificationMessage = sprintf(
                           'Your maintenance request "%s" has been updated to: %s by %s on %s',
                           $maintenance->title,
                           $statusText,
                           $pmName,
                           $updateDateTime
                       );
   
                       $this->notificationModel->createNotification([
                           'user_id' => $maintenance->landlord_id,
                           'type' => 'maintenance_update',
                           'title' => 'Maintenance Request Status Updated',
                           'message' => $notificationMessage,
                           'link' => 'maintenance/details/' . $id
                       ]);
                   }
   
                   flash('maintenance_message', 'Status updated successfully', 'alert alert-success');
               } else {
                   flash('maintenance_message', 'Failed to update status', 'alert alert-danger');
               }
   
               redirect('maintenance/details/' . $id);
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

            redirect('maintenance/details/' . $id);
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

    // Upload quotation (Property Manager only)
    public function uploadQuotation($request_id)
    {
        // Ensure only property managers can upload quotations
        if ($_SESSION['user_type'] !== 'property_manager') {
            flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
            redirect('maintenance/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'request_id' => $request_id,
                'provider_id' => trim($_POST['provider_id']),
                'uploaded_by' => $_SESSION['user_id'],
                'amount' => trim($_POST['amount']),
                'description' => trim($_POST['description']),
                'quotation_file' => null,
                'status' => 'pending'
            ];

            // Validate required fields
            if (empty($data['provider_id']) || empty($data['amount']) || empty($data['description'])) {
                flash('maintenance_message', 'Please fill in all required fields', 'alert alert-danger');
                redirect('maintenance/details/' . $request_id);
            }

            // Handle file upload if provided
            if (isset($_FILES['quotation_file']) && $_FILES['quotation_file']['error'] == 0) {
                $upload_dir = 'public/uploads/quotations/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['quotation_file']['name'], PATHINFO_EXTENSION);
                $file_name = 'quotation_' . $request_id . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['quotation_file']['tmp_name'], $file_path)) {
                    $data['quotation_file'] = $file_name;
                }
            }

            // Create quotation
            if ($this->quotationModel->createQuotation($data)) {
                // Send notification to landlord
                $maintenance = $this->maintenanceModel->getMaintenanceById($request_id);
                $this->notificationModel->createNotification([
                    'user_id' => $maintenance->landlord_id,
                    'type' => 'maintenance_quotation',
                    'title' => 'New Maintenance Quotation',
                    'message' => 'A quotation has been uploaded for your maintenance request: ' . $maintenance->title,
                    'link' => '/maintenance/details/' . $request_id
                ]);

                flash('maintenance_message', 'Quotation uploaded successfully', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Failed to upload quotation', 'alert alert-danger');
            }

            redirect('maintenance/details/' . $request_id);
        }
    }

    // Approve quotation (Landlord only)
    public function approveQuotation($quotation_id)
    {
        // Ensure only landlords can approve quotations
        if ($_SESSION['user_type'] !== 'landlord') {
            flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
            redirect('maintenance/index');
        }

        $quotation = $this->quotationModel->getQuotationById($quotation_id);

        if (!$quotation) {
            flash('maintenance_message', 'Quotation not found', 'alert alert-danger');
            redirect('maintenance/index');
        }

        // Verify this is landlord's request
        $maintenance = $this->maintenanceModel->getMaintenanceById($quotation->request_id);
        if ($maintenance->landlord_id != $_SESSION['user_id']) {
            flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
            redirect('maintenance/index');
        }

        if ($this->quotationModel->approveQuotation($quotation_id, $_SESSION['user_id'])) {
            flash('maintenance_message', 'Quotation approved. Please proceed with payment.', 'alert alert-success');
        } else {
            flash('maintenance_message', 'Failed to approve quotation', 'alert alert-danger');
        }

        redirect('maintenance/details/' . $quotation->request_id);
    }

    // Reject quotation (Landlord only)
    public function rejectQuotation($quotation_id)
    {
        // Ensure only landlords can reject quotations
        if ($_SESSION['user_type'] !== 'landlord') {
            flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
            redirect('maintenance/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $quotation = $this->quotationModel->getQuotationById($quotation_id);

            if (!$quotation) {
                flash('maintenance_message', 'Quotation not found', 'alert alert-danger');
                redirect('maintenance/index');
            }

            // Verify this is landlord's request
            $maintenance = $this->maintenanceModel->getMaintenanceById($quotation->request_id);
            if ($maintenance->landlord_id != $_SESSION['user_id']) {
                flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
                redirect('maintenance/index');
            }

            $rejection_reason = trim($_POST['rejection_reason']);

            if ($this->quotationModel->rejectQuotation($quotation_id, $rejection_reason)) {
                flash('maintenance_message', 'Quotation rejected', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Failed to reject quotation', 'alert alert-danger');
            }

            redirect('maintenance/details/' . $quotation->request_id);
        }
    }

    // Pay for quotation (Landlord only)
    public function payQuotation($quotation_id)
    {
        // Ensure only landlords can pay quotations
        if ($_SESSION['user_type'] !== 'landlord') {
            flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
            redirect('maintenance/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $quotation = $this->quotationModel->getQuotationById($quotation_id);

            if (!$quotation || $quotation->status !== 'approved') {
                flash('maintenance_message', 'Invalid quotation', 'alert alert-danger');
                redirect('maintenance/index');
            }

            // Verify this is landlord's request
            $maintenance = $this->maintenanceModel->getMaintenanceById($quotation->request_id);
            if ($maintenance->landlord_id != $_SESSION['user_id']) {
                flash('maintenance_message', 'Unauthorized access', 'alert alert-danger');
                redirect('maintenance/index');
            }

            // Check if already paid
            if ($this->quotationModel->isQuotationPaid($quotation_id)) {
                flash('maintenance_message', 'Quotation already paid', 'alert alert-warning');
                redirect('maintenance/details/' . $quotation->request_id);
            }

            $payment_data = [
                'request_id' => $quotation->request_id,
                'quotation_id' => $quotation_id,
                'landlord_id' => $_SESSION['user_id'],
                'amount' => $quotation->amount,
                'payment_method' => trim($_POST['payment_method']),
                'transaction_id' => trim($_POST['transaction_id']) ?: null,
                'status' => 'completed',
                'payment_date' => date('Y-m-d H:i:s'),
                'notes' => trim($_POST['notes']) ?: ''
            ];

            if ($this->quotationModel->createPayment($payment_data)) {
                // Update maintenance request status to 'scheduled' or 'in_progress'
                $this->maintenanceModel->updateMaintenanceStatus($quotation->request_id, 'scheduled');

                // Send notification to property manager
                $property = $this->propertyModel->getPropertyById($maintenance->property_id);
                if ($property && $property->manager_id) {
                    $this->notificationModel->createNotification([
                        'user_id' => $property->manager_id,
                        'type' => 'maintenance_payment',
                        'title' => 'Maintenance Payment Received',
                        'message' => 'Payment received for maintenance request: ' . $maintenance->title . '. Please coordinate with the service provider.',
                        'link' => '/maintenance/details/' . $quotation->request_id
                    ]);
                }

                flash('maintenance_message', 'Payment successful! The property manager has been notified.', 'alert alert-success');
            } else {
                flash('maintenance_message', 'Payment failed. Please try again.', 'alert alert-danger');
            }

            redirect('maintenance/details/' . $quotation->request_id);
        }
    }
}
