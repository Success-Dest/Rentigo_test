<?php
class Inspections extends Controller
{
    private $M_Inspection;

    public function __construct()
    {
        // Check if user is logged in and is a manager
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'property_manager') {
            redirect('users/login');
        }

        $this->M_Inspection = $this->model('M_Inspection');
    }

    // Show all inspections for this manager
    public function index()
    {
        $inspections = $this->M_Inspection->getInspectionsByManager($_SESSION['user_id']);

        $data = [
            'title' => 'Property Inspections',
            'page' => 'inspections',
            'inspections' => $inspections,
            'user_name' => $_SESSION['user_name']
        ];

        $this->view('manager/v_inspections', $data);
    }

    // AJAX endpoint to get issues by property
    public function getIssuesByProperty($property_id = null)
    {
        header('Content-Type: application/json');

        if (!$property_id) {
            echo json_encode(['success' => false, 'message' => 'Property ID is required']);
            exit;
        }

        $issues = $this->M_Inspection->getIssuesByPropertyId($property_id);

        if ($issues) {
            echo json_encode(['success' => true, 'issues' => $issues]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No issues found']);
        }
        exit;
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            // Initialize validator
            $validator = new Validator();

            // Prepare data
            $data = [
                'property_id' => (int)($_POST['property_id'] ?? 0),
                'type' => trim($_POST['type'] ?? ''),
                'issue_id' => !empty($_POST['issue_id']) ? (int)$_POST['issue_id'] : 0,
                'date' => trim($_POST['date'] ?? ''),
                'time' => trim($_POST['time'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'property_address' => '',
                'property_id_err' => '',
                'type_err' => '',
                'issue_id_err' => '',
                'date_err' => '',
                'time_err' => '',
                'notes_err' => ''
            ];

            // Validate property_id
            $validator->required('property_id', $data['property_id'], 'Please select a property');

            if ($data['property_id'] > 0) {
                $property = $this->M_Inspection->getPropertyById($data['property_id']);
                if (!$property) {
                    $validator->custom('property_id', false, 'Selected property does not exist');
                } else {
                    $data['property_address'] = $property->address;
                }
            }

            // Validate type
            $validTypes = ['routine', 'move_in', 'move_out', 'maintenance', 'annual', 'emergency', 'issue'];
            if ($validator->required('type', $data['type'], 'Inspection type is required')) {
                $validator->inArray('type', $data['type'], $validTypes, 'Please select a valid inspection type');
            }

            // Validate issue_id (optional - can be 0 for routine inspections)
            if ($data['issue_id'] > 0) {
                $issues = $this->M_Inspection->getIssuesByPropertyId($data['property_id']);
                $issueFound = false;

                if ($issues) {
                    foreach ($issues as $issue) {
                        if ($issue->id == $data['issue_id']) {
                            $issueFound = true;
                            break;
                        }
                    }
                }

                if (!$issueFound) {
                    $validator->custom('issue_id', false, 'Selected issue does not belong to this property');
                }
            }

            // ✅ FIXED: Validate date with correct logic
            if ($validator->required('date', $data['date'], 'Inspection date is required')) {
                // Check if date is valid format
                $dateTime = DateTime::createFromFormat('Y-m-d', $data['date']);
                $isValidDate = $dateTime && $dateTime->format('Y-m-d') === $data['date'];

                if (!$isValidDate) {
                    $validator->custom('date', false, 'Please enter a valid date');
                } else {
                    // ✅ FIXED: Check if date is today or in the future
                    $selectedDate = strtotime($data['date'] . ' 00:00:00');
                    $today = strtotime(date('Y-m-d') . ' 00:00:00');

                    // Allow today's date or future dates
                    if ($selectedDate < $today) {
                        $validator->custom('date', false, 'Inspection date cannot be in the past');
                    }

                    // Check if date is not too far in the future (max 1 year)
                    $oneYearFromNow = strtotime('+1 year', $today);
                    if ($selectedDate > $oneYearFromNow) {
                        $validator->custom('date', false, 'Inspection date cannot be more than 1 year in the future');
                    }
                }
            }

            // Check if validation passed
            if ($validator->hasErrors()) {
                $errors = $validator->getErrors();

                $viewData = [
                    'title' => 'Schedule Inspection',
                    'page' => 'add_inspection',
                    'user_name' => $_SESSION['user_name'],
                    'properties' => $this->M_Inspection->getPropertiesWithIssues(),
                    'property_id' => $data['property_id'],
                    'type' => $data['type'],
                    'issue_id' => $data['issue_id'],
                    'date' => $data['date'],
                    'property_id_err' => $errors['property_id'] ?? '',
                    'type_err' => $errors['type'] ?? '',
                    'issue_id_err' => $errors['issue_id'] ?? '',
                    'date_err' => $errors['date'] ?? ''
                ];

                $this->view('manager/v_add_inspection', $viewData);
                return;
            }

            // Get property details for landlord and tenant info
            $property = $this->M_Inspection->getPropertyById($data['property_id']);

            // Prepare data for insertion
            $insertData = [
                'property_id' => $data['property_id'],
                'issue_id' => $data['issue_id'],
                'type' => $data['type'],
                'date' => $data['date'],
                'time' => $data['time'],
                'notes' => $data['notes'],
                'manager_id' => $_SESSION['user_id'],
                'landlord_id' => $property->landlord_id ?? null,
                'tenant_id' => $property->tenant_id ?? null
            ];

            // Attempt to add inspection
            if ($inspection_id = $this->M_Inspection->addInspection($insertData)) {
                // Send notifications
                $notificationModel = $this->model('M_Notifications');

                $inspectionType = ucfirst(str_replace('_', ' ', $data['type']));
                $scheduleInfo = date('M d, Y', strtotime($data['date']));
                if ($data['time']) {
                    $scheduleInfo .= ' at ' . date('g:i A', strtotime($data['time']));
                }

                // Notify landlord
                if ($property->landlord_id) {
                    $notificationModel->createNotification([
                        'user_id' => $property->landlord_id,
                        'type' => 'inspection_scheduled',
                        'title' => 'Inspection Scheduled',
                        'message' => "A {$inspectionType} inspection has been scheduled for your property at {$property->address} on {$scheduleInfo}.",
                        'link' => 'landlord/inspections'
                    ]);
                }

                // Notify tenant
                if ($property->tenant_id) {
                    $notificationModel->createNotification([
                        'user_id' => $property->tenant_id,
                        'type' => 'inspection_scheduled',
                        'title' => 'Inspection Scheduled',
                        'message' => "A {$inspectionType} inspection has been scheduled for your property at {$property->address} on {$scheduleInfo}.",
                        'link' => 'tenant/inspections'
                    ]);
                }

                flash('inspection_message', 'Inspection scheduled successfully. Landlord and tenant have been notified.', 'alert alert-success');
                redirect('inspections/index');
            } else {
                flash('inspection_message', 'Failed to schedule inspection. Please try again.', 'alert alert-danger');
                redirect('inspections/add');
            }
        } else {
            // GET request → show form
            $properties = $this->M_Inspection->getAllPropertiesByManager($_SESSION['user_id']);

            $data = [
                'title' => 'Schedule Inspection',
                'page' => 'add_inspection',
                'user_name' => $_SESSION['user_name'],
                'properties' => $properties,
                'property_id' => '',
                'type' => '',
                'issue_id' => '',
                'date' => '',
                'time' => '',
                'notes' => '',
                'property_id_err' => '',
                'type_err' => '',
                'issue_id_err' => '',
                'date_err' => '',
                'time_err' => '',
                'notes_err' => ''
            ];

            $this->view('manager/v_add_inspection', $data);
        }
    }

    // Edit inspection
    public function edit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            // Initialize validator
            $validator = new Validator();

            // Prepare data
            $data = [
                'id' => $id,
                'property_id' => (int)($_POST['property_id'] ?? 0),
                'type' => trim($_POST['type'] ?? ''),
                'issue_id' => !empty($_POST['issue_id']) ? (int)$_POST['issue_id'] : 0,
                'date' => trim($_POST['date'] ?? ''),
                'time' => trim($_POST['time'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'inspection_notes' => trim($_POST['inspection_notes'] ?? ''),
                'status' => trim($_POST['status'] ?? ''),
                'property_address' => '',
                'property_id_err' => '',
                'type_err' => '',
                'issue_id_err' => '',
                'date_err' => '',
                'time_err' => '',
                'notes_err' => '',
                'inspection_notes_err' => '',
                'status_err' => ''
            ];

            // Validate property_id
            $validator->required('property_id', $data['property_id'], 'Please select a property');

            if ($data['property_id'] > 0) {
                $property = $this->M_Inspection->getPropertyById($data['property_id']);
                if (!$property) {
                    $validator->custom('property_id', false, 'Selected property does not exist');
                } else {
                    $data['property_address'] = $property->address;
                }
            }

            // Validate type
            $validTypes = ['routine', 'move_in', 'move_out', 'maintenance', 'annual', 'emergency', 'issue'];
            if ($validator->required('type', $data['type'], 'Inspection type is required')) {
                $validator->inArray('type', $data['type'], $validTypes, 'Please select a valid inspection type');
            }

            // Validate status
            $validStatuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
            if ($validator->required('status', $data['status'], 'Inspection status is required')) {
                $validator->inArray('status', $data['status'], $validStatuses, 'Please select a valid status');
            }

            // Validate issue_id (optional)
            if ($data['issue_id'] > 0) {
                $issues = $this->M_Inspection->getIssuesByPropertyId($data['property_id']);
                $issueFound = false;

                if ($issues) {
                    foreach ($issues as $issue) {
                        if ($issue->id == $data['issue_id']) {
                            $issueFound = true;
                            break;
                        }
                    }
                }

                if (!$issueFound) {
                    $validator->custom('issue_id', false, 'Selected issue does not belong to this property');
                }
            }

            // ✅ FIXED: Validate date
            if ($validator->required('date', $data['date'], 'Inspection date is required')) {
                // Check if date is valid format
                $dateTime = DateTime::createFromFormat('Y-m-d', $data['date']);
                $isValidDate = $dateTime && $dateTime->format('Y-m-d') === $data['date'];

                if (!$isValidDate) {
                    $validator->custom('date', false, 'Please enter a valid date');
                } else {
                    // For completed inspections, allow past dates
                    if ($data['status'] !== 'completed' && $data['status'] !== 'cancelled') {
                        $selectedDate = strtotime($data['date'] . ' 00:00:00');
                        $today = strtotime(date('Y-m-d') . ' 00:00:00');

                        if ($selectedDate < $today) {
                            $validator->custom('date', false, 'Inspection date cannot be in the past (unless marking as completed or cancelled)');
                        }
                    }
                }
            }

            // Check if validation passed
            if ($validator->hasErrors()) {
                $errors = $validator->getErrors();

                $inspection = $this->M_Inspection->getInspectionById($id);

                $viewData = [
                    'title' => 'Edit Inspection',
                    'page' => 'edit_inspection',
                    'user_name' => $_SESSION['user_name'],
                    'inspection' => $inspection,
                    'property_id' => $data['property_id'],
                    'type' => $data['type'],
                    'issue_id' => $data['issue_id'],
                    'date' => $data['date'],
                    'status' => $data['status'],
                    'property_id_err' => $errors['property_id'] ?? '',
                    'type_err' => $errors['type'] ?? '',
                    'issue_id_err' => $errors['issue_id'] ?? '',
                    'date_err' => $errors['date'] ?? '',
                    'status_err' => $errors['status'] ?? ''
                ];

                $this->view('manager/v_edit_inspection', $viewData);
                return;
            }

            // Prepare data for update
            $updateData = [
                'property_id' => $data['property_id'],
                'type' => $data['type'],
                'issue_id' => $data['issue_id'],
                'date' => $data['date'],
                'time' => $data['time'],
                'notes' => $data['notes'],
                'inspection_notes' => $data['inspection_notes'],
                'status' => $data['status']
            ];

            // Attempt to update inspection
            if ($this->M_Inspection->updateInspection($id, $updateData)) {
                flash('inspection_message', 'Inspection updated successfully', 'alert alert-success');
                redirect('inspections/index');
            } else {
                flash('inspection_message', 'Failed to update inspection. Please try again.', 'alert alert-danger');
                redirect('inspections/edit/' . $id);
            }
        } else {
            // GET request → show edit form
            $inspection = $this->M_Inspection->getInspectionById($id);

            if (!$inspection) {
                flash('inspection_message', 'Inspection not found', 'alert alert-danger');
                redirect('inspections/index');
                return;
            }

            $data = [
                'title' => 'Edit Inspection',
                'page' => 'edit_inspection',
                'user_name' => $_SESSION['user_name'],
                'inspection' => $inspection,
                'property_id_err' => '',
                'type_err' => '',
                'issue_id_err' => '',
                'date_err' => '',
                'status_err' => ''
            ];

            $this->view('manager/v_edit_inspection', $data);
        }
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Verify inspection exists
            $inspection = $this->M_Inspection->getInspectionById($id);

            if (!$inspection) {
                flash('inspection_message', 'Inspection not found', 'alert alert-danger');
                redirect('inspections/index');
                return;
            }

            // Attempt to delete
            if ($this->M_Inspection->deleteInspection($id)) {
                flash('inspection_message', 'Inspection deleted successfully', 'alert alert-success');
                redirect('inspections/index');
            } else {
                flash('inspection_message', 'Failed to delete inspection. Please try again.', 'alert alert-danger');
                redirect('inspections/index');
            }
        } else {
            flash('inspection_message', 'Invalid request method', 'alert alert-danger');
            // ✅ FIXED: Typo corrected from 'inspectiosn' to 'inspections'
            redirect('inspections/index');
        }
    }
}
