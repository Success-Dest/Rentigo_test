<?php
require_once '../app/helpers/helper.php';

class Issues extends Controller
{
    private $issueModel;

    public function __construct()
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'tenant') {
            redirect('users/login');
        }

        $this->issueModel = $this->model('Issue');
    }

    public function report()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'tenant_id' => $_SESSION['user_id'],
                'property_id' => trim($_POST['property_id']),
                'issue_title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'category' => trim($_POST['category']),
                'priority' => trim($_POST['priority']),
                'status' => 'pending'
            ];

            $validator = new Validator();

            $validator->required('property', $data['property_id'], 'Please select a property');

            if ($validator->required('title', $data['issue_title'])) {
                $validator->minLength('title', $data['issue_title'], 5);
                $validator->maxLength('title', $data['issue_title'], 200);
            }

            if ($validator->required('description', $data['description'])) {
                $validator->minLength('description', $data['description'], 10);
                $validator->maxLength('description', $data['description'], 1000);
            }

            $validCategories = ['plumbing', 'electrical', 'hvac', 'appliance', 'structural', 'pest', 'other'];
            if ($validator->required('category', $data['category'], 'Please select a category')) {
                $validator->inArray('category', $data['category'], $validCategories);
            }

            $validPriorities = ['low', 'medium', 'high', 'emergency'];
            if ($validator->required('priority', $data['priority'], 'Please select a priority')) {
                $validator->inArray('priority', $data['priority'], $validPriorities);
            }

            if ($data['priority'] === 'emergency') {
                $validator->custom(
                    'description',
                    strlen($data['description']) >= 50,
                    'Emergency issues require detailed description (at least 50 characters)'
                );
            }

            $criticalCategories = ['electrical', 'structural'];
            if (in_array($data['category'], $criticalCategories) && $data['priority'] === 'low') {
                $validator->custom(
                    'priority',
                    false,
                    ucfirst($data['category']) . ' issues should not be low priority for safety reasons'
                );
            }

            if (!$validator->hasErrors()) {
                // Get property details to find landlord
                $propertyModel = $this->model('M_Properties');
                $property = $propertyModel->getPropertyById($data['property_id']);

                $dbData = [
                    'tenant_id' => $data['tenant_id'],
                    'property_id' => $data['property_id'],
                    'title' => $data['issue_title'],
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'priority' => $data['priority'],
                    'status' => $data['status'],
                    'landlord_id' => $property->landlord_id ?? null
                ];

                if ($issue_id = $this->issueModel->addIssue($dbData)) {
                    // Send notification to PM
                    $managerModel = $this->model('M_ManagerProperties');
                    $manager = $managerModel->getManagerByProperty($data['property_id']);

                    if ($manager) {
                        $notificationModel = $this->model('M_Notifications');
                        $notificationModel->createNotification([
                            'user_id' => $manager->manager_id,
                            'type' => 'issue_reported',
                            'title' => 'New Issue Reported',
                            'message' => 'A tenant has reported a new ' . $data['priority'] . ' priority issue: ' . $data['issue_title'],
                            'link' => 'manager/issueDetails/' . $issue_id
                        ]);
                    }

                    flash('issue_message', 'Issue reported successfully. Property manager has been notified.', 'alert alert-success');
                    redirect('issues/track');
                } else {
                    die('Something went wrong when saving issue');
                }
            } else {
                $errors = $validator->getErrors();
                $data['property_err'] = $errors['property'] ?? '';
                $data['title_err'] = $errors['title'] ?? '';
                $data['description_err'] = $errors['description'] ?? '';
                $data['category_err'] = $errors['category'] ?? '';
                $data['priority_err'] = $errors['priority'] ?? '';

                $data['properties'] = $this->issueModel->getProperties();
                $data['recentIssues'] = $this->issueModel->getRecentIssuesByTenant($_SESSION['user_id'], 2);
                $data['page_title'] = 'Report Issues - TenantHub';
                $data['page'] = 'report_issue';
                $data['user_name'] = $_SESSION['user_name'];

                $this->view('tenant/v_report_issue', $data);
            }
        } else {
            $properties = $this->issueModel->getProperties();
            $recentIssues = $this->issueModel->getRecentIssuesByTenant($_SESSION['user_id'], 2);

            $data = [
                'page_title' => 'Report Issues - TenantHub',
                'page' => 'report_issue',
                'user_name' => $_SESSION['user_name'],
                'properties' => $properties,
                'recentIssues' => $recentIssues,
                'property_id' => '',
                'issue_title' => '',
                'description' => '',
                'category' => '',
                'priority' => '',
                'property_err' => '',
                'title_err' => '',
                'description_err' => '',
                'category_err' => '',
                'priority_err' => ''
            ];

            $this->view('tenant/v_report_issue', $data);
        }
    }

    public function track()
    {
        $issues = $this->issueModel->getIssuesByTenant($_SESSION['user_id']);

        $pending_issues = 0;
        $in_progress_issues = 0;
        $resolved_issues = 0;

        foreach ($issues as $issue) {
            switch ($issue->status) {
                case 'pending':
                    $pending_issues++;
                    break;
                case 'in_progress':
                case 'assigned':
                    $in_progress_issues++;
                    break;
                case 'resolved':
                    $resolved_issues++;
                    break;
            }
        }

        $data = [
            'page_title' => 'Track Issues - TenantHub',
            'page' => 'track_issues',
            'user_name' => $_SESSION['user_name'],
            'issues' => $issues,
            'pending_issues' => $pending_issues,
            'in_progress_issues' => $in_progress_issues,
            'resolved_issues' => $resolved_issues
        ];

        $this->view('tenant/v_track_issues', $data);
    }

    public function edit($id = null)
    {
        if (!$id) {
            flash('issue_message', 'Invalid issue ID', 'alert alert-danger');
            redirect('issues/track');
        }

        // Get the issue first to check time restriction
        $issue = $this->issueModel->getIssueById($id);

        if (!$issue) {
            flash('issue_message', 'Issue not found', 'alert alert-danger');
            redirect('issues/track');
        }

        if ($issue->tenant_id != $_SESSION['user_id']) {
            flash('issue_message', 'Unauthorized access', 'alert alert-danger');
            redirect('issues/track');
        }

        // Check if issue can be edited (within 1 minute of creation)
        $createdTime = new DateTime($issue->created_at);
        $currentTime = new DateTime();
        $timeDiff = $currentTime->getTimestamp() - $createdTime->getTimestamp();

        if ($timeDiff > 60) {
            flash('issue_message', 'Issues can only be edited within 1 minute of creation', 'alert alert-warning');
            redirect('issues/track');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'tenant_id' => $_SESSION['user_id'],
                'property_id' => trim($_POST['property_id']),
                'issue_title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'category' => trim($_POST['category']),
                'priority' => trim($_POST['priority']),
                'status' => trim($_POST['status'])
            ];

            $validator = new Validator();

            $validator->required('property', $data['property_id'], 'Please select a property');

            if ($validator->required('title', $data['issue_title'])) {
                $validator->minLength('title', $data['issue_title'], 5);
                $validator->maxLength('title', $data['issue_title'], 200);
            }

            if ($validator->required('description', $data['description'])) {
                $validator->minLength('description', $data['description'], 10);
                $validator->maxLength('description', $data['description'], 1000);
            }

            $validCategories = ['plumbing', 'electrical', 'hvac', 'appliance', 'structural', 'pest', 'other'];
            if ($validator->required('category', $data['category'], 'Please select a category')) {
                $validator->inArray('category', $data['category'], $validCategories);
            }

            $validPriorities = ['low', 'medium', 'high', 'emergency'];
            if ($validator->required('priority', $data['priority'], 'Please select a priority')) {
                $validator->inArray('priority', $data['priority'], $validPriorities);
            }

            if ($data['priority'] === 'emergency') {
                $validator->custom(
                    'description',
                    strlen($data['description']) >= 50,
                    'Emergency issues require detailed description (at least 50 characters)'
                );
            }

            $criticalCategories = ['electrical', 'structural'];
            if (in_array($data['category'], $criticalCategories) && $data['priority'] === 'low') {
                $validator->custom(
                    'priority',
                    false,
                    ucfirst($data['category']) . ' issues should not be low priority for safety reasons'
                );
            }

            if (!$validator->hasErrors()) {
                $dbData = [
                    'id' => $data['id'],
                    'property_id' => $data['property_id'],
                    'title' => $data['issue_title'],
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'priority' => $data['priority'],
                    'status' => $data['status']
                ];

                if ($this->issueModel->updateIssue($dbData)) {
                    flash('issue_message', 'Issue updated successfully', 'alert alert-success');
                    redirect('issues/track');
                } else {
                    die('Something went wrong when updating issue');
                }
            } else {
                $errors = $validator->getErrors();
                $data['property_err'] = $errors['property'] ?? '';
                $data['title_err'] = $errors['title'] ?? '';
                $data['description_err'] = $errors['description'] ?? '';
                $data['category_err'] = $errors['category'] ?? '';
                $data['priority_err'] = $errors['priority'] ?? '';

                $data['issue'] = $issue;
                $data['properties'] = $this->issueModel->getProperties();
                $data['page_title'] = 'Edit Issue - TenantHub';
                $data['page'] = 'edit_issue';
                $data['user_name'] = $_SESSION['user_name'];

                $this->view('tenant/v_edit_issue', $data);
            }
        } else {
            $properties = $this->issueModel->getProperties();

            $data = [
                'page_title' => 'Edit Issue - TenantHub',
                'page' => 'edit_issue',
                'user_name' => $_SESSION['user_name'],
                'issue' => $issue,
                'properties' => $properties,
                'property_id' => $issue->property_id,
                'issue_title' => $issue->title,
                'description' => $issue->description,
                'category' => $issue->category,
                'priority' => $issue->priority,
                'property_err' => '',
                'title_err' => '',
                'description_err' => '',
                'category_err' => '',
                'priority_err' => ''
            ];

            $this->view('tenant/v_edit_issue', $data);
        }
    }

    public function delete($id = null)
    {
        if (!$id) {
            flash('issue_message', 'Invalid issue ID', 'alert alert-danger');
            redirect('issues/track');
        }

        $issue = $this->issueModel->getIssueById($id);

        if (!$issue) {
            flash('issue_message', 'Issue not found', 'alert alert-danger');
            redirect('issues/track');
        }

        if ($issue->tenant_id != $_SESSION['user_id']) {
            flash('issue_message', 'Unauthorized action', 'alert alert-danger');
            redirect('issues/track');
        }

        $createdTime = new DateTime($issue->created_at);
        $currentTime = new DateTime();
        $timeDiff = $currentTime->getTimestamp() - $createdTime->getTimestamp();

        if ($timeDiff > 60) {
            flash('issue_message', 'Issues can only be deleted within 1 minute of creation', 'alert alert-warning');
            redirect('issues/track');
        }

        if ($this->issueModel->deleteIssue($id)) {
            flash('issue_message', 'Issue deleted successfully', 'alert alert-success');
            redirect('issues/track');
        } else {
            flash('issue_message', 'Something went wrong. Unable to delete issue.', 'alert alert-danger');
            redirect('issues/track');
        }
    }
}
