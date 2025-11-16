<?php
class TenantProperties extends Controller
{
    private $tenantPropertyModel;
    private $propertyModel;
    private $notificationModel;

    public function __construct()
    {
        // Optionally require tenant login:
        // if (!isLoggedIn() || $_SESSION['user_type'] !== 'tenant') {
        //     redirect('users/login');
        // }

        $this->tenantPropertyModel = $this->model('M_TenantProperties');
        $this->propertyModel = $this->model('M_Properties');
        $this->notificationModel = $this->model('M_Notifications');
    }

    // List all approved and available properties
    public function index()
    {
        $properties = $this->tenantPropertyModel->getApprovedProperties();

        // Optionally add images, etc.
        if ($properties) {
            foreach ($properties as $property) {
                $property->primary_image = $this->getPrimaryPropertyImage($property->id);
            }
        }

        $data = [
            'properties' => $properties,
            'page' => 'search_properties',
        ];
        $this->view('tenant/v_search_properties', $data);
    }

    // View property details
    public function details($id)
    {
        $property = $this->tenantPropertyModel->getPropertyById($id);

        if (!$property) {
            flash('tenant_property_message', 'Property not found or not available', 'alert alert-danger');
            redirect('tenantproperties/index');
            return;
        }

        // Optionally fetch images, docs, etc.
        $property->images = $this->getPropertyImages($property->id);
        $property->documents = $this->getPropertyDocuments($property->id);

        // Load reviews and ratings
        $reviewModel = $this->model('M_Reviews');
        $reviews = $reviewModel->getReviewsByProperty($id, 'approved');
        $ratingData = $reviewModel->getPropertyAverageRating($id);

        // Safely extract rating data
        $averageRating = 0;
        $reviewCount = 0;
        if ($ratingData) {
            $averageRating = $ratingData->avg_rating ?? 0;
            $reviewCount = $ratingData->review_count ?? 0;
        }

        $data = [
            'property' => $property,
            'reviews' => $reviews ?? [],
            'averageRating' => $averageRating,
            'reviewCount' => $reviewCount
        ];
        $this->view('tenant/v_property_details', $data);
    }

    // (Optional) AJAX or form-based search endpoint
    public function search()
    {
        // If using AJAX, receive parameters and return JSON or filtered view
        // Not implemented here—client-side filtering used in view.
        $this->index();
    }

    // Reserve property - Step 1 (Tenant)
    public function reserve($id)
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'tenant') {
            flash('reservation_message', 'Only tenants can reserve properties', 'alert alert-danger');
            redirect('users/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $property = $this->propertyModel->getPropertyById($id);

            if (!$property) {
                flash('reservation_message', 'Property not found', 'alert alert-danger');
                redirect('tenantproperties/index');
                return;
            }

            // Check if property is available
            if ($property->status !== 'available') {
                flash('reservation_message', 'This property is not available for reservation', 'alert alert-danger');
                redirect('tenantproperties/details/' . $id);
                return;
            }

            // Update property status to reserved
            if ($this->propertyModel->updatePropertyStatus($id, 'reserved')) {
                // Send notification to tenant
                $this->notificationModel->createNotification([
                    'user_id' => $_SESSION['user_id'],
                    'type' => 'property',
                    'title' => 'Property Reserved Successfully',
                    'message' => 'Your reservation for "' . substr($property->address, 0, 50) . '..." has been confirmed. Please visit our office to proceed with the property viewing and booking process.',
                    'link' => 'tenantproperties/details/' . $id
                ]);

                flash('reservation_message', 'Property reserved successfully! Please visit our office to view the property and proceed with booking.', 'alert alert-success');
                redirect('tenant/dashboard');
            } else {
                flash('reservation_message', 'Failed to reserve property. Please try again.', 'alert alert-danger');
                redirect('tenantproperties/details/' . $id);
            }
        } else {
            redirect('tenantproperties/index');
        }
    }

    // Helpers (reuse your existing image/document helpers)
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

        // Fallback: get the first image if exists
        $images = $this->getPropertyImages($propertyId);
        if (!empty($images)) {
            return $images[0]['url'];
        }

        return URLROOT . '/img/property-placeholder.jpg';
    }

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
