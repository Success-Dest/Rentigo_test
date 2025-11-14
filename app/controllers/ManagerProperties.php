<?php
class ManagerProperties extends Controller
{
    private $managerPropertyModel;

    public function __construct()
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'property_manager') {
            redirect('users/login');
        }

        $this->managerPropertyModel = $this->model('M_ManagerProperties');
    }

    // List all properties assigned to this manager
    public function index()
    {
        $manager_id = $_SESSION['user_id'];
        $properties = $this->managerPropertyModel->getAssignedProperties($manager_id);

        // If you want to load images, documents, etc for cards, add here
        if ($properties) {
            foreach ($properties as $property) {
                $property->images = $this->getPropertyImages($property->id);
                $property->primary_image = $this->getPrimaryPropertyImage($property->id);
            }
        }

        $data = [
            'properties' => $properties,
            'page' => 'properties'
        ];

        $this->view('manager/v_properties', $data);
    }

    // View details for a single assigned property
    public function details($id)
    {
        $manager_id = $_SESSION['user_id'];
        $property = $this->managerPropertyModel->getPropertyById($id, $manager_id);

        if (!$property) {
            flash('manager_property_message', 'Property not found or not assigned to you', 'alert alert-danger');
            redirect('managerproperties/index');
            return;
        }

        // Optionally load images, documents, etc
        $property->images = $this->getPropertyImages($property->id);
        $property->documents = $this->getPropertyDocuments($property->id);

        $data = [
            'property' => $property,
            'page' => 'manager_properties'
        ];

        $this->view('manager/v_property_details', $data);
    }

    // Optionally, reuse helpers from landlord/admin controllers for image/document gallery:
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

    private function getPrimaryPropertyImage($propertyId)
    {
        $propertyDir = APPROOT . '/../public/uploads/properties/property_' . $propertyId . '/';
        $primaryFile = $propertyDir . 'primary.txt';
        $urlBase = URLROOT . '/uploads/properties/property_' . $propertyId . '/';

        if (file_exists($primaryFile)) {
            $primaryImageName = trim(file_get_contents($primaryFile));
            if ($primaryImageName && file_exists($propertyDir . $primaryImageName)) {
                return $urlBase . $primaryImageName;
            }
        }

        $images = $this->getPropertyImages($propertyId);
        if (!empty($images)) {
            return $images[0]['url'];
        }

        return URLROOT . '/img/property-placeholder.jpg';
    }

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
}
