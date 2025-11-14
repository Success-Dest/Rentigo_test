<?php
require_once '../app/helpers/helper.php';

class Properties extends Controller
{
    private $propertyModel;
    private $rentOptimizer;

    public function __construct()
    {
        if (!isLoggedIn() || $_SESSION['user_type'] !== 'landlord') {
            redirect('users/login');
        }

        $this->propertyModel = $this->model('M_Properties');
        $this->rentOptimizer = $this->model('M_RentOptimizer');
    }

    public function index()
    {
        $properties = $this->propertyModel->getPropertiesByLandlord($_SESSION['user_id']);

        if ($properties) {
            foreach ($properties as $property) {
                $property->images = $this->getPropertyImages($property->id);
                $property->documents = $this->getPropertyDocuments($property->id);
                $property->primary_image = $this->getPrimaryPropertyImage($property->id);
                // approval_status is expected to be included in property object
            }
        }

        $data = [
            'properties' => $properties,
            'page' => 'properties'
        ];
        $this->view('landlord/v_properties', $data);
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $uploadError = $this->checkUploadLimits();
            if ($uploadError) {
                flash('property_message', $uploadError, 'alert alert-danger');
                redirect('properties/add');
                return;
            }

            if (empty($_POST)) {
                flash('property_message', 'Form data was not received. This might be due to file size limits.', 'alert alert-danger');
                redirect('properties/add');
                return;
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $listingType = $_POST['listing_type'] ?? 'rent';

            if ($listingType === 'maintenance') {
                $result = $this->processMaintenanceProperty($_POST);
            } else {
                $result = $this->processRentalProperty($_POST);
            }

            // Check if validation failed
            if (!$result['valid']) {
                // Reload view with errors
                $data = $result['data'];
                $data['max_file_size'] = $this->getMaxFileSize();
                $data['max_post_size'] = $this->getMaxPostSize();
                $this->view('landlord/v_add_property', $data);
                return;
            }

            $data = $result['data'];

            // PHASE 3: Always set approval_status to 'pending' when adding a new property
            $data['approval_status'] = 'pending';

            $propertyId = $this->propertyModel->addPropertyAndReturnId($data);

            if ($propertyId) {
                $imageUploadResult = $this->handleImageUploads($propertyId);
                $documentUploadResult = $this->handleDocumentUploads($propertyId);

                $messages = [];
                if ($imageUploadResult['success'] && $imageUploadResult['count'] > 0) {
                    $messages[] = $imageUploadResult['count'] . ' images uploaded';
                }
                if ($documentUploadResult['success'] && $documentUploadResult['count'] > 0) {
                    $messages[] = $documentUploadResult['count'] . ' documents uploaded';
                }

                $propertyTypeText = $listingType === 'maintenance' ? 'Maintenance property' : 'Rental property';

                if (!empty($messages)) {
                    flash('property_message', $propertyTypeText . ' added successfully with ' . implode(' and ', $messages), 'alert alert-success');
                } else {
                    flash('property_message', $propertyTypeText . ' added successfully', 'alert alert-success');
                }

                $warnings = [];
                if (!$imageUploadResult['success'] && $imageUploadResult['count'] == 0) {
                    $warnings[] = 'Image upload failed: ' . $imageUploadResult['message'];
                }
                if (!$documentUploadResult['success'] && $documentUploadResult['count'] == 0) {
                    $warnings[] = 'Document upload failed: ' . $documentUploadResult['message'];
                }

                if (!empty($warnings)) {
                    flash('property_warning', implode('. ', $warnings), 'alert alert-warning');
                }

                redirect('properties/index');
            } else {
                flash('property_message', 'Failed to add property. Please check all fields and try again.', 'alert alert-danger');
                redirect('properties/add');
            }
        } else {
            // GET request
            $maxFileSize = $this->getMaxFileSize();
            $maxPostSize = $this->getMaxPostSize();

            $data = [
                'title' => 'Add Property',
                'max_file_size' => $maxFileSize,
                'max_post_size' => $maxPostSize,
                'address' => '',
                'type' => '',
                'bedrooms' => '',
                'bathrooms' => '',
                'sqft' => '',
                'rent' => '',
                'deposit' => '',
                'available_date' => '',
                'parking' => '0',
                'pets' => 'no',
                'laundry' => 'none',
                'description' => '',
                'address_err' => '',
                'type_err' => '',
                'bedrooms_err' => '',
                'bathrooms_err' => '',
                'sqft_err' => '',
                'rent_err' => '',
                'deposit_err' => ''
            ];

            $this->view('landlord/v_add_property', $data);
        }
    }

    private function processRentalProperty($postData)
    {
        $validator = new Validator();

        $data = [
            'landlord_id'   => $_SESSION['user_id'],
            'address'       => trim($postData['address'] ?? ''),
            'property_type' => trim($postData['type'] ?? ''),
            'bedrooms'      => (int)($postData['bedrooms'] ?? 0),
            'bathrooms'     => (float)($postData['bathrooms'] ?? 0),
            'sqft'          => !empty($postData['sqft']) ? (int)$postData['sqft'] : null,
            'rent'          => (float)($postData['rent'] ?? 0),
            'deposit'       => !empty($postData['deposit']) ? (float)$postData['deposit'] : null,
            'available_date' => !empty($postData['available_date']) ? $postData['available_date'] : null,
            'parking'       => isset($postData['parking']) && is_numeric($postData['parking']) ? (int)$postData['parking'] : 0,
            'pet_policy'    => trim($postData['pets'] ?? 'no'),
            'laundry'       => trim($postData['laundry'] ?? 'none'),
            'description'   => trim($postData['description'] ?? ''),
            'status'        => 'available',
            'listing_type'  => 'rent'
        ];

        // Validate address
        if ($validator->required('address', $data['address'])) {
            $validator->minLength('address', $data['address'], 5, 'Address must be at least 5 characters');
            $validator->maxLength('address', $data['address'], 255, 'Address must not exceed 255 characters');
        }

        // Validate property type
        $validTypes = ['apartment', 'house', 'condo', 'townhouse'];
        if ($validator->required('type', $data['property_type'], 'Property type is required')) {
            $validator->inArray('type', $data['property_type'], $validTypes, 'Please select a valid property type');
        }

        // Validate bedrooms
        $validator->required('bedrooms', $data['bedrooms'], 'Number of bedrooms is required');
        $validator->custom('bedrooms', $data['bedrooms'] >= 0, 'Number of bedrooms cannot be negative');

        // Validate bathrooms
        $validator->required('bathrooms', $data['bathrooms'], 'Number of bathrooms is required');
        $validator->custom('bathrooms', $data['bathrooms'] > 0, 'Number of bathrooms is required');

        // Validate square footage (optional)
        if ($data['sqft'] !== null) {
            $validator->custom('sqft', $data['sqft'] >= 1 && $data['sqft'] <= 50000, 'Square footage must be between 1 and 50,000 sq ft');
        }

        // Validate rent
        if ($validator->required('rent', $data['rent'], 'Monthly rent is required')) {
            $validator->custom('rent', $data['rent'] >= 1000, 'Monthly rent must be at least Rs 1,000');
            $validator->custom('rent', $data['rent'] <= 10000000, 'Monthly rent cannot exceed Rs 10,000,000');
        }

        // Validate deposit (optional)
        if ($data['deposit'] !== null) {
            $validator->custom('deposit', $data['deposit'] >= 0, 'Security deposit cannot be negative');
            $validator->custom('deposit', $data['deposit'] <= 10000000, 'Security deposit cannot exceed Rs 10,000,000');

            // Warn if deposit > 6 months rent
            if ($data['deposit'] > ($data['rent'] * 6)) {
                error_log("Warning: Security deposit (" . $data['deposit'] . ") is more than 6 months rent (" . $data['rent'] . ") for property");
            }
        }

        // Validate pet policy
        $validPetPolicies = ['no', 'cats', 'dogs', 'both'];
        $validator->inArray('pets', $data['pet_policy'], $validPetPolicies, 'Please select a valid pet policy');

        // Validate laundry
        $validLaundry = ['none', 'shared', 'hookups', 'in_unit', 'included'];
        $validator->inArray('laundry', $data['laundry'], $validLaundry, 'Please select a valid laundry option');

        // Check validation
        if ($validator->hasErrors()) {
            $errors = $validator->getErrors();
            return [
                'valid' => false,
                'data' => array_merge($data, [
                    'address_err' => $errors['address'] ?? '',
                    'type_err' => $errors['type'] ?? '',
                    'bedrooms_err' => $errors['bedrooms'] ?? '',
                    'bathrooms_err' => $errors['bathrooms'] ?? '',
                    'sqft_err' => $errors['sqft'] ?? '',
                    'rent_err' => $errors['rent'] ?? '',
                    'deposit_err' => $errors['deposit'] ?? '',
                    'pets_err' => $errors['pets'] ?? '',
                    'laundry_err' => $errors['laundry'] ?? ''
                ])
            ];
        }

        return ['valid' => true, 'data' => $data];
    }

    private function processMaintenanceProperty($postData)
    {
        $validator = new Validator();

        $data = [
            'landlord_id'   => $_SESSION['user_id'],
            'address'       => trim($postData['address'] ?? ''),
            'property_type' => trim($postData['type'] ?? ''),
            'bedrooms'      => (int)($postData['bedrooms'] ?? 0),
            'bathrooms'     => (float)($postData['bathrooms'] ?? 1),
            'sqft'          => !empty($postData['sqft']) ? (int)$postData['sqft'] : null,
            'rent'          => 0,
            'deposit'       => null,
            'available_date' => null,
            'parking'       => 0,
            'pet_policy'    => 'no',
            'laundry'       => 'none',
            'description'   => trim($postData['description'] ?? ''),
            'status'        => 'maintenance',
            'listing_type'  => 'maintenance',
            'current_occupant' => trim($postData['current_occupant'] ?? '')
        ];

        // Validate address
        if ($validator->required('address', $data['address'])) {
            $validator->minLength('address', $data['address'], 5);
            $validator->maxLength('address', $data['address'], 255);
        }

        // Validate property type
        $validTypes = ['apartment', 'house', 'condo', 'townhouse', 'commercial', 'land', 'other'];
        if ($validator->required('type', $data['property_type'], 'Property type is required')) {
            $validator->inArray('type', $data['property_type'], $validTypes);
        }

        // Validate square footage (optional)
        if ($data['sqft'] !== null) {
            $validator->custom('sqft', $data['sqft'] >= 1 && $data['sqft'] <= 50000, 'Square footage must be between 1 and 50,000 sq ft');
        }

        // Check validation
        if ($validator->hasErrors()) {
            $errors = $validator->getErrors();
            return [
                'valid' => false,
                'data' => array_merge($data, [
                    'address_err' => $errors['address'] ?? '',
                    'type_err' => $errors['type'] ?? '',
                    'sqft_err' => $errors['sqft'] ?? ''
                ])
            ];
        }

        return ['valid' => true, 'data' => $data];
    }

    private function checkUploadLimits()
    {
        if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $maxPostSize = $this->getMaxPostSize();
            $contentLength = $_SERVER['CONTENT_LENGTH'];
            return "Upload failed: Total data size (" . $this->formatBytes($contentLength) . ") exceeds server limit (" . $this->formatBytes($maxPostSize) . "). Please reduce image file sizes or upload fewer files.";
        }

        if (isset($_FILES['photos']['error']) && is_array($_FILES['photos']['error'])) {
            foreach ($_FILES['photos']['error'] as $error) {
                if ($error == UPLOAD_ERR_FORM_SIZE || $error == UPLOAD_ERR_INI_SIZE) {
                    $maxFileSize = $this->getMaxFileSize();
                    return "Upload failed: One or more files exceed the maximum file size limit (" . $this->formatBytes($maxFileSize) . "). Please reduce image file sizes.";
                }
            }
        }

        return null;
    }

    private function getMaxFileSize()
    {
        $maxUpload = $this->parseSize(ini_get('upload_max_filesize'));
        $maxPost = $this->parseSize(ini_get('post_max_size'));
        return min($maxUpload, $maxPost);
    }

    private function getMaxPostSize()
    {
        return $this->parseSize(ini_get('post_max_size'));
    }

    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function handleImageUploads($propertyId)
    {
        if (
            !isset($_FILES['photos']) ||
            !isset($_FILES['photos']['name']) ||
            !is_array($_FILES['photos']['name']) ||
            empty($_FILES['photos']['name']) ||
            empty($_FILES['photos']['name'][0])
        ) {
            return ['success' => true, 'count' => 0, 'message' => 'No images to upload'];
        }

        $uploadBaseDir = APPROOT . '/../public/uploads/properties/';
        $propertyDir = $uploadBaseDir . 'property_' . $propertyId . '/';

        if (!is_dir($uploadBaseDir)) {
            if (!mkdir($uploadBaseDir, 0755, true)) {
                error_log("Failed to create base upload directory: " . $uploadBaseDir);
                return ['success' => false, 'count' => 0, 'message' => 'Failed to create base upload directory'];
            }
        }

        if (!is_dir($propertyDir)) {
            if (!mkdir($propertyDir, 0755, true)) {
                error_log("Failed to create property directory: " . $propertyDir);
                return ['success' => false, 'count' => 0, 'message' => 'Failed to create property directory'];
            }
        }

        $uploadedCount = 0;
        $errors = [];

        $fileNames = $_FILES['photos']['name'];
        if (!is_array($fileNames)) {
            $fileNames = [$fileNames];
        }

        $totalFiles = count($fileNames);
        $maxImages = min($totalFiles, 5);

        for ($i = 0; $i < $maxImages; $i++) {
            if (!isset($_FILES['photos']['error'][$i]) || $_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
                $errorMsg = $this->getUploadErrorMessage($_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
                $errors[] = "Upload error for file " . ($i + 1) . ": " . $errorMsg;
                continue;
            }

            $tmpName = $_FILES['photos']['tmp_name'][$i];
            $originalName = $_FILES['photos']['name'][$i];

            if (empty($originalName)) {
                continue;
            }

            if (!file_exists($tmpName) || !is_readable($tmpName)) {
                $errors[] = "File not accessible: " . $originalName;
                continue;
            }

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileType = finfo_file($finfo, $tmpName);
                finfo_close($finfo);
            } else {
                $fileType = mime_content_type($tmpName);
            }

            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Invalid file type for: " . $originalName . " (Type: " . $fileType . ")";
                continue;
            }

            if ($_FILES['photos']['size'][$i] > 2 * 1024 * 1024) {
                $errors[] = "File too large: " . $originalName . " (" . $this->formatBytes($_FILES['photos']['size'][$i]) . "). Max 2MB per image.";
                continue;
            }

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $filename = 'img_' . date('Y-m-d_H-i-s') . '_' . $i . '.' . $extension;
            $filePath = $propertyDir . $filename;

            if (move_uploaded_file($tmpName, $filePath)) {
                $uploadedCount++;

                if ($uploadedCount === 1) {
                    $primaryFile = $propertyDir . 'primary.txt';
                    file_put_contents($primaryFile, $filename);
                }

                error_log("Successfully uploaded image: " . $filename . " for property " . $propertyId);
            } else {
                $errors[] = "Failed to upload: " . $originalName;
                error_log("Failed to move uploaded file from " . $tmpName . " to " . $filePath);
            }
        }

        if ($uploadedCount > 0) {
            $message = $uploadedCount . " images uploaded successfully";
            if (!empty($errors)) {
                $message .= ". Issues: " . implode(', ', $errors);
            }
            return [
                'success' => true,
                'count' => $uploadedCount,
                'message' => $message
            ];
        } else {
            return [
                'success' => false,
                'count' => 0,
                'message' => !empty($errors) ? implode(', ', $errors) : 'No images were uploaded'
            ];
        }
    }

    private function handleDocumentUploads($propertyId)
    {
        if (
            !isset($_FILES['documents']) ||
            !isset($_FILES['documents']['name']) ||
            !is_array($_FILES['documents']['name']) ||
            empty($_FILES['documents']['name']) ||
            empty($_FILES['documents']['name'][0])
        ) {
            return ['success' => true, 'count' => 0, 'message' => 'No documents to upload'];
        }

        $uploadBaseDir = APPROOT . '/../public/uploads/properties/';
        $propertyDir = $uploadBaseDir . 'property_' . $propertyId . '/documents/';

        if (!is_dir($propertyDir)) {
            if (!mkdir($propertyDir, 0755, true)) {
                error_log("Failed to create property documents directory: " . $propertyDir);
                return ['success' => false, 'count' => 0, 'message' => 'Failed to create documents directory'];
            }
        }

        $uploadedCount = 0;
        $errors = [];

        $fileNames = $_FILES['documents']['name'];
        if (!is_array($fileNames)) {
            $fileNames = [$fileNames];
        }

        $totalFiles = count($fileNames);
        $maxDocuments = min($totalFiles, 3);

        for ($i = 0; $i < $maxDocuments; $i++) {
            if (!isset($_FILES['documents']['error'][$i]) || $_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) {
                $errorMsg = $this->getUploadErrorMessage($_FILES['documents']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
                $errors[] = "Upload error for document " . ($i + 1) . ": " . $errorMsg;
                continue;
            }

            $tmpName = $_FILES['documents']['tmp_name'][$i];
            $originalName = $_FILES['documents']['name'][$i];

            if (empty($originalName)) {
                continue;
            }

            if (!file_exists($tmpName) || !is_readable($tmpName)) {
                $errors[] = "Document not accessible: " . $originalName;
                continue;
            }

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileType = finfo_file($finfo, $tmpName);
                finfo_close($finfo);
            } else {
                $fileType = mime_content_type($tmpName);
            }

            $allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif'
            ];
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Invalid document type for: " . $originalName . " (Type: " . $fileType . ")";
                continue;
            }

            if ($_FILES['documents']['size'][$i] > 5 * 1024 * 1024) {
                $errors[] = "Document too large: " . $originalName . " (" . $this->formatBytes($_FILES['documents']['size'][$i]) . "). Max 5MB per document.";
                continue;
            }

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $filename = 'doc_' . date('Y-m-d_H-i-s') . '_' . $i . '.' . $extension;
            $filePath = $propertyDir . $filename;

            if (move_uploaded_file($tmpName, $filePath)) {
                $uploadedCount++;
                error_log("Successfully uploaded document: " . $filename . " for property " . $propertyId);
            } else {
                $errors[] = "Failed to upload document: " . $originalName;
                error_log("Failed to move uploaded document from " . $tmpName . " to " . $filePath);
            }
        }

        if ($uploadedCount > 0) {
            $message = $uploadedCount . " documents uploaded successfully";
            if (!empty($errors)) {
                $message .= ". Issues: " . implode(', ', $errors);
            }
            return [
                'success' => true,
                'count' => $uploadedCount,
                'message' => $message
            ];
        } else {
            return [
                'success' => false,
                'count' => 0,
                'message' => !empty($errors) ? implode(', ', $errors) : 'No documents were uploaded'
            ];
        }
    }

    private function getUploadErrorMessage($error)
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize (' . ini_get('upload_max_filesize') . ')';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE';
            case UPLOAD_ERR_PARTIAL:
                return 'File partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'No temporary directory';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Cannot write to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload stopped by extension';
            default:
                return 'Unknown upload error (' . $error . ')';
        }
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

    public function getImagesJson($propertyId)
    {
        header('Content-Type: application/json');

        if (!$propertyId) {
            echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
            return;
        }

        $images = $this->getPropertyImages($propertyId);

        echo json_encode([
            'success' => true,
            'images' => $images
        ]);
    }

    public function suggestRent()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('properties/index');
        }

        header('Content-Type: application/json');

        try {
            $propertyData = [
                'address' => trim($_POST['address'] ?? ''),
                'property_type' => trim($_POST['property_type'] ?? ''),
                'bedrooms' => (int)($_POST['bedrooms'] ?? 0),
                'bathrooms' => (float)($_POST['bathrooms'] ?? 0),
                'sqft' => !empty($_POST['sqft']) ? (int)$_POST['sqft'] : null,
                'parking' => trim($_POST['parking'] ?? '0'),
                'pet_policy' => trim($_POST['pet_policy'] ?? 'no'),
                'laundry' => trim($_POST['laundry'] ?? 'none')
            ];

            if (empty($propertyData['property_type']) || empty($propertyData['bedrooms'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Property type and bedrooms are required to generate a suggestion.'
                ]);
                exit;
            }

            $suggestion = $this->rentOptimizer->suggestRent($propertyData);

            echo json_encode($suggestion);
            exit;
        } catch (Exception $e) {
            error_log('Rent suggestion error: ' . $e->getMessage());

            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while analyzing market data. Please try again or enter rent manually.'
            ]);
            exit;
        }
    }

    public function edit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // ============================================
            // FORM SUBMISSION
            // ============================================

            $uploadError = $this->checkUploadLimits();
            if ($uploadError) {
                flash('property_message', $uploadError, 'alert alert-danger');
                redirect('properties/edit/' . $id);
                return;
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $existingProperty = $this->propertyModel->getPropertyById($id);

            if (!$existingProperty) {
                flash('property_message', 'Property not found');
                redirect('properties/index');
                return;
            }

            $validator = new Validator();

            if ($existingProperty->listing_type === 'maintenance') {
                $data = [
                    'id' => $id,
                    'address' => trim($_POST['address'] ?? ''),
                    'property_type' => trim($_POST['property_type'] ?? ''),
                    'bedrooms' => (int)($existingProperty->bedrooms ?? 0),
                    'bathrooms' => (float)($existingProperty->bathrooms ?? 1),
                    'sqft' => !empty($_POST['sqft']) ? (int)$_POST['sqft'] : null,
                    'rent' => 0,
                    'deposit' => null,
                    'available_date' => null,
                    'parking' => 0,
                    'pet_policy' => 'no',
                    'laundry' => 'none',
                    'description' => trim($_POST['description'] ?? ''),
                    'current_occupant' => trim($_POST['current_occupant'] ?? '')
                ];

                // Validate maintenance property
                if ($validator->required('address', $data['address'])) {
                    $validator->minLength('address', $data['address'], 5);
                }

                $validTypes = ['apartment', 'house', 'condo', 'townhouse', 'commercial', 'land', 'other'];
                if ($validator->required('property_type', $data['property_type'], 'Property type is required')) {
                    $validator->inArray('property_type', $data['property_type'], $validTypes);
                }

                if ($data['sqft'] !== null) {
                    $validator->custom('sqft', $data['sqft'] >= 1 && $data['sqft'] <= 50000, 'Square footage must be between 1 and 50,000 sq ft');
                }
            } else {
                $data = [
                    'id' => $id,
                    'address' => trim($_POST['address'] ?? ''),
                    'property_type' => trim($_POST['property_type'] ?? ''),
                    'bedrooms' => (int)($_POST['bedrooms'] ?? 0),
                    'bathrooms' => (float)($_POST['bathrooms'] ?? 0),
                    'sqft' => !empty($_POST['sqft']) ? (int)$_POST['sqft'] : null,
                    'rent' => (float)($_POST['rent'] ?? 0),
                    'deposit' => !empty($_POST['deposit']) ? (float)$_POST['deposit'] : null,
                    'available_date' => !empty($_POST['available_date']) ? $_POST['available_date'] : null,
                    'parking' => trim($_POST['parking'] ?? ''),
                    'pet_policy' => trim($_POST['pets'] ?? 'no'),
                    'laundry' => trim($_POST['laundry'] ?? 'none'),
                    'description' => trim($_POST['description'] ?? ''),
                ];

                // Validate rental property
                if ($validator->required('address', $data['address'])) {
                    $validator->minLength('address', $data['address'], 5);
                }

                $validTypes = ['apartment', 'house', 'condo', 'townhouse'];
                if ($validator->required('property_type', $data['property_type'], 'Property type is required')) {
                    $validator->inArray('property_type', $data['property_type'], $validTypes);
                }

                $validator->required('bedrooms', $data['bedrooms'], 'Number of bedrooms is required');
                $validator->custom('bedrooms', $data['bedrooms'] >= 0, 'Bedrooms cannot be negative');

                $validator->required('bathrooms', $data['bathrooms'], 'Number of bathrooms is required');
                $validator->custom('bathrooms', $data['bathrooms'] > 0, 'Bathrooms must be at least 1');

                if ($validator->required('rent', $data['rent'], 'Monthly rent is required')) {
                    $validator->custom('rent', $data['rent'] >= 1000, 'Rent must be at least Rs 1,000');
                    $validator->custom('rent', $data['rent'] <= 10000000, 'Rent cannot exceed Rs 10,000,000');
                }

                if ($data['deposit'] !== null) {
                    $validator->custom('deposit', $data['deposit'] >= 0, 'Deposit cannot be negative');
                    $validator->custom('deposit', $data['deposit'] <= 10000000, 'Deposit cannot exceed Rs 10,000,000');
                }

                if ($data['sqft'] !== null) {
                    $validator->custom('sqft', $data['sqft'] >= 1 && $data['sqft'] <= 50000, 'Square footage must be between 1 and 50,000 sq ft');
                }
            }

            // Check validation
            if ($validator->hasErrors()) {
                $errors = $validator->getErrors();
                $existingProperty->images = $this->getPropertyImages($id);
                $existingProperty->documents = $this->getPropertyDocuments($id);

                $viewData = [
                    'property' => $existingProperty,
                    'address_err' => $errors['address'] ?? '',
                    'type_err' => $errors['property_type'] ?? '',
                    'bedrooms_err' => $errors['bedrooms'] ?? '',
                    'bathrooms_err' => $errors['bathrooms'] ?? '',
                    'sqft_err' => $errors['sqft'] ?? '',
                    'rent_err' => $errors['rent'] ?? '',
                    'deposit_err' => $errors['deposit'] ?? ''
                ];

                $this->view('landlord/v_edit_properties', $viewData);
                return;
            }

            // Update database
            if ($this->propertyModel->update($data)) {
                // Handle new image uploads
                if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                    $imageUploadResult = $this->handleImageUploads($id);
                    if ($imageUploadResult['success'] && $imageUploadResult['count'] > 0) {
                        flash('property_message', 'Property updated successfully with ' . $imageUploadResult['count'] . ' new images', 'alert alert-success');
                    } else {
                        flash('property_message', 'Property updated successfully', 'alert alert-success');
                    }
                }

                // Handle new document uploads
                if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
                    $documentUploadResult = $this->handleDocumentUploads($id);
                }

                if (!isset($_FILES['photos']) || empty($_FILES['photos']['name'][0])) {
                    flash('property_message', 'Property Updated Successfully', 'alert alert-success');
                }

                redirect('properties/index');
            } else {
                flash('property_message', 'Failed to update property', 'alert alert-danger');
                redirect('properties/edit/' . $id);
            }
        } else {
            // ============================================
            // DISPLAY FORM
            // ============================================

            $property = $this->propertyModel->getPropertyById($id);

            if (!$property) {
                flash('property_message', 'Property not found');
                redirect('properties/index');
            }

            $property->images = $this->getPropertyImages($id);
            $property->documents = $this->getPropertyDocuments($id);

            $data = [
                'property' => $property,
                'address_err' => '',
                'type_err' => '',
                'bedrooms_err' => '',
                'bathrooms_err' => '',
                'sqft_err' => '',
                'rent_err' => '',
                'deposit_err' => ''
            ];

            $this->view('landlord/v_edit_properties', $data);
        }
    }

    // ✅ NEW METHOD: Delete existing image
    public function deleteImage()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        header('Content-Type: application/json');

        $imageName = $_POST['image_name'] ?? '';
        $propertyId = $_POST['property_id'] ?? 0;

        if (empty($imageName) || empty($propertyId)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        // Verify property belongs to logged-in landlord
        $property = $this->propertyModel->getPropertyById($propertyId);
        if (!$property || $property->landlord_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Construct file path
        $propertyDir = APPROOT . '/../public/uploads/properties/property_' . $propertyId . '/';
        $filePath = $propertyDir . $imageName;

        // Check if file exists and delete it
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                // Check if this was the primary image
                $primaryFile = $propertyDir . 'primary.txt';
                if (file_exists($primaryFile)) {
                    $primaryImageName = trim(file_get_contents($primaryFile));
                    if ($primaryImageName === $imageName) {
                        // Delete primary.txt or set new primary
                        $remainingImages = $this->getPropertyImages($propertyId);
                        if (!empty($remainingImages)) {
                            file_put_contents($primaryFile, $remainingImages[0]['name']);
                        } else {
                            unlink($primaryFile);
                        }
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete image file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Image file not found']);
        }
        exit;
    }

    // ✅ NEW METHOD: Delete existing document
    public function deleteDocument()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        header('Content-Type: application/json');

        $documentName = $_POST['document_name'] ?? '';
        $propertyId = $_POST['property_id'] ?? 0;

        if (empty($documentName) || empty($propertyId)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        // Verify property belongs to logged-in landlord
        $property = $this->propertyModel->getPropertyById($propertyId);
        if (!$property || $property->landlord_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Construct file path
        $documentPath = APPROOT . '/../public/uploads/properties/property_' . $propertyId . '/documents/' . $documentName;

        // Check if file exists and delete it
        if (file_exists($documentPath)) {
            if (unlink($documentPath)) {
                echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete document file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Document file not found']);
        }
        exit;
    }

    public function delete($id)
    {
        $this->deletePropertyImages($id);

        if ($this->propertyModel->deleteProperty($id)) {
            flash('property_message', 'Property and all associated images removed');
            redirect('properties/index');
        } else {
            die('Something went wrong');
        }
    }

    private function deletePropertyImages($propertyId)
    {
        $propertyDir = APPROOT . '/../public/uploads/properties/property_' . $propertyId . '/';

        if (is_dir($propertyDir)) {
            $this->deleteDirectory($propertyDir);
        }
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $dir . $file;
                if (is_dir($filePath)) {
                    $this->deleteDirectory($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }

        return rmdir($dir);
    }
}
