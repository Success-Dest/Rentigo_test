<?php
class AdminProperties extends Controller
{
    private $adminPropertyModel;
    private $notificationModel;

    public function __construct()
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'admin') {
            redirect('users/login');
        }
        $this->adminPropertyModel = $this->model('M_AdminProperties');
        $this->notificationModel = $this->model('M_Notifications');
    }

    public function index()
    {
        $filter = $_GET['filter'] ?? 'all';
        $validFilters = ['all', 'pending', 'approved', 'rejected'];
        if (!in_array($filter, $validFilters)) {
            $filter = 'all';
        }

        if ($filter === 'all') {
            $properties = $this->adminPropertyModel->getAllProperties();
        } else {
            $properties = $this->adminPropertyModel->getAllProperties($filter);
        }

        $counts = $this->adminPropertyModel->getPropertyCounts();

        $data = [
            'title' => 'Manage Properties - Admin',
            'page' => 'properties',
            'properties' => is_array($properties) ? $properties : [],
            'counts' => $counts,
            'current_filter' => $filter ?? 'all',
            'user_name' => $_SESSION['user_name']
        ];

        $this->view('admin/v_properties', $data);
    }

    public function propertyDetails($id)
    {
        $property = $this->adminPropertyModel->getPropertyById($id);

        if (!$property) {
            flash('admin_property_message', 'Property not found', 'alert alert-danger');
            redirect('adminproperties/index');
            return;
        }

        $property->images = $this->getPropertyImages($id);
        $property->documents = $this->getPropertyDocuments($id);

        $managers = $this->adminPropertyModel->getApprovedPropertyManagers();

        $data = [
            'title' => 'Property Details - Admin',
            'page' => 'properties',
            'property' => $property,
            'managers' => $managers,
            'user_name' => $_SESSION['user_name']
        ];

        $this->view('admin/v_admin_property_details', $data);
    }

    // Helper to get property images
    private function getPropertyImages($propertyId)
    {
        $propertyDir = APPROOT . '/../public/uploads/properties/property_' . $propertyId . '/';
        $urlBase = URLROOT . '/uploads/properties/property_' . $propertyId . '/';

        if (!is_dir($propertyDir)) {
            return [];
        }

        $images = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $files = scandir($propertyDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'primary.txt' || is_dir($propertyDir . $file)) {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions)) {
                $images[] = [
                    'name' => $file,
                    'url' => $urlBase . $file,
                    'path' => $propertyDir . $file,
                    'size' => filesize($propertyDir . $file),
                    'modified' => filemtime($propertyDir . $file)
                ];
            }
        }

        usort($images, function ($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $images;
    }

    // Helper to get property documents
    private function getPropertyDocuments($propertyId)
    {
        $documentsDir = APPROOT . '/../public/uploads/properties/property_' . $propertyId . '/documents/';
        $urlBase = URLROOT . '/uploads/properties/property_' . $propertyId . '/documents/';

        if (!is_dir($documentsDir)) {
            return [];
        }

        $documents = [];
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

        $files = scandir($documentsDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions)) {
                $documents[] = [
                    'name' => $file,
                    'url' => $urlBase . $file,
                    'path' => $documentsDir . $file,
                    'size' => filesize($documentsDir . $file),
                    'modified' => filemtime($documentsDir . $file),
                    'type' => $extension
                ];
            }
        }

        usort($documents, function ($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $documents;
    }

    public function approve($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $property = $this->adminPropertyModel->getPropertyById($id);

            if (!$property) {
                flash('admin_property_message', 'Property not found', 'alert alert-danger');
                redirect('adminproperties/index');
                return;
            }

            if ($property->approval_status === 'approved') {
                flash('admin_property_message', 'Property is already approved', 'alert alert-warning');
                redirect('adminproperties/propertyDetails/' . $id);
                return;
            }

            if ($this->adminPropertyModel->approveProperty($id)) {
                // Send notification to landlord
                $this->notificationModel->createNotification([
                    'user_id' => $property->landlord_id,
                    'type' => 'property',
                    'title' => 'Property Approved',
                    'message' => 'Your property at "' . substr($property->address, 0, 50) . '..." has been approved and is now live!',
                    'link' => 'properties/index'
                ]);

                flash('admin_property_message', 'Property approved successfully!', 'alert alert-success');
                redirect('adminproperties/propertyDetails/' . $id);
            } else {
                flash('admin_property_message', 'Failed to approve property', 'alert alert-danger');
                redirect('adminproperties/propertyDetails/' . $id);
            }
        } else {
            redirect('adminproperties/index');
        }
    }

    public function reject($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $property = $this->adminPropertyModel->getPropertyById($id);

            if (!$property) {
                flash('admin_property_message', 'Property not found', 'alert alert-danger');
                redirect('adminproperties/index');
                return;
            }

            if ($this->adminPropertyModel->rejectProperty($id)) {
                // Send notification to landlord
                $this->notificationModel->createNotification([
                    'user_id' => $property->landlord_id,
                    'type' => 'property',
                    'title' => 'Property Rejected',
                    'message' => 'Your property at "' . substr($property->address, 0, 50) . '..." has been rejected. Please contact support for more information.',
                    'link' => 'properties/index'
                ]);

                flash('admin_property_message', 'Property rejected', 'alert alert-success');
                redirect('adminproperties/propertyDetails/' . $id);
            } else {
                flash('admin_property_message', 'Failed to reject property', 'alert alert-danger');
                redirect('adminproperties/propertyDetails/' . $id);
            }
        } else {
            redirect('adminproperties/index');
        }
    }

    public function assign($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $manager_id = (int)($_POST['manager_id'] ?? 0);

            if ($manager_id <= 0) {
                flash('admin_property_message', 'Please select a property manager', 'alert alert-danger');
                redirect('adminproperties/propertyDetails/' . $id);
                return;
            }

            if ($this->adminPropertyModel->assignPropertyToManager($id, $manager_id)) {
                // Get property details for notification
                $property = $this->adminPropertyModel->getPropertyById($id);

                // Send notification to property manager
                $this->notificationModel->createNotification([
                    'user_id' => $manager_id,
                    'type' => 'property',
                    'title' => 'New Property Assigned',
                    'message' => 'You have been assigned to manage property at "' . substr($property->address, 0, 50) . '..."',
                    'link' => 'managerproperties/details/' . $id
                ]);

                flash('admin_property_message', 'Property assigned successfully!', 'alert alert-success');
                redirect('adminproperties/propertyDetails/' . $id);
            } else {
                flash('admin_property_message', 'Failed to assign property', 'alert alert-danger');
                redirect('adminproperties/propertyDetails/' . $id);
            }
        } else {
            redirect('adminproperties/index');
        }
    }

    public function unassign($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->adminPropertyModel->unassignProperty($id)) {
                flash('admin_property_message', 'Property unassigned successfully!', 'alert alert-success');
                redirect('adminproperties/propertyDetails/' . $id);
            } else {
                flash('admin_property_message', 'Failed to unassign property', 'alert alert-danger');
                redirect('adminproperties/propertyDetails/' . $id);
            }
        } else {
            redirect('adminproperties/index');
        }
    }

    // NEW: Delete property (and all images/documents)
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $property = $this->adminPropertyModel->getPropertyById($id);

            if (!$property) {
                flash('admin_property_message', 'Property not found', 'alert alert-danger');
                redirect('adminproperties/index');
                return;
            }

            // Delete property images & documents from disk
            $propertyDir = APPROOT . '/../public/uploads/properties/property_' . $id . '/';
            $this->deleteDirectory($propertyDir);

            // Delete from DB
            if ($this->adminPropertyModel->deleteProperty($id)) {
                flash('admin_property_message', 'Property and all associated files deleted successfully.', 'alert alert-success');
            } else {
                flash('admin_property_message', 'Failed to delete property from database.', 'alert alert-danger');
            }
            redirect('adminproperties/index');
        } else {
            redirect('adminproperties/index');
        }
    }

    // Helper to recursively delete property folder
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
