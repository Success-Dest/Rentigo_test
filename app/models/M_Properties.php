<?php
class M_Properties
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get properties by landlord
    public function getPropertiesByLandlord($landlordId)
    {
        $this->db->query('SELECT * FROM properties WHERE landlord_id = :landlord_id ORDER BY created_at DESC');
        $this->db->bind(':landlord_id', $landlordId);

        return $this->db->resultSet();
    }

    // Get property by ID
    public function getPropertyById($id)
    {
        $this->db->query('SELECT * FROM properties WHERE id = :id');
        $this->db->bind(':id', $id);

        return $this->db->single();
    }

    // Add property (existing method - kept for backward compatibility)
    public function addProperty($data)
    {
        $this->db->query('INSERT INTO properties (
            landlord_id, address, property_type, bedrooms, bathrooms, sqft, rent, deposit,
            available_date, parking, pet_policy, laundry, description, status, listing_type
        ) VALUES (
            :landlord_id, :address, :property_type, :bedrooms, :bathrooms, :sqft, :rent, :deposit,
            :available_date, :parking, :pet_policy, :laundry, :description, :status, :listing_type
        )');

        // Bind values
        $this->db->bind(':landlord_id', $data['landlord_id']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':property_type', $data['property_type']);
        $this->db->bind(':bedrooms', $data['bedrooms']);
        $this->db->bind(':bathrooms', $data['bathrooms']);
        $this->db->bind(':sqft', $data['sqft']);
        $this->db->bind(':rent', $data['rent']);
        $this->db->bind(':deposit', $data['deposit']);
        $this->db->bind(':available_date', $data['available_date']);
        $this->db->bind(':parking', $data['parking']);
        $this->db->bind(':pet_policy', $data['pet_policy']);
        $this->db->bind(':laundry', $data['laundry']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':listing_type', $data['listing_type'] ?? 'rental');

        return $this->db->execute();
    }

    // Add property and return ID
    public function addPropertyAndReturnId($data)
    {
        $this->db->query('INSERT INTO properties (
            landlord_id, address, property_type, bedrooms, bathrooms, sqft, rent, deposit,
            available_date, parking, pet_policy, laundry, description, status, listing_type
        ) VALUES (
            :landlord_id, :address, :property_type, :bedrooms, :bathrooms, :sqft, :rent, :deposit,
            :available_date, :parking, :pet_policy, :laundry, :description, :status, :listing_type
        )');

        // Bind values
        $this->db->bind(':landlord_id', $data['landlord_id']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':property_type', $data['property_type']);
        $this->db->bind(':bedrooms', $data['bedrooms']);
        $this->db->bind(':bathrooms', $data['bathrooms']);
        $this->db->bind(':sqft', $data['sqft']);
        $this->db->bind(':rent', $data['rent']);
        $this->db->bind(':deposit', $data['deposit']);
        $this->db->bind(':available_date', $data['available_date']);
        $this->db->bind(':parking', $data['parking']);
        $this->db->bind(':pet_policy', $data['pet_policy']);
        $this->db->bind(':laundry', $data['laundry']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':listing_type', $data['listing_type'] ?? 'rental');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    // Update property
    public function update($data)
    {
        $this->db->query('UPDATE properties SET
                         address = :address,
                         property_type = :property_type,
                         bedrooms = :bedrooms,
                         bathrooms = :bathrooms,
                         sqft = :sqft,
                         rent = :rent,
                         deposit = :deposit,
                         available_date = :available_date,
                         parking = :parking,
                         pet_policy = :pet_policy,
                         laundry = :laundry,
                         description = :description
                         WHERE id = :id');

        // Bind values
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':property_type', $data['property_type']);
        $this->db->bind(':bedrooms', $data['bedrooms']);
        $this->db->bind(':bathrooms', $data['bathrooms']);
        $this->db->bind(':sqft', $data['sqft']);
        $this->db->bind(':rent', $data['rent']);
        $this->db->bind(':deposit', $data['deposit']);
        $this->db->bind(':available_date', $data['available_date']);
        $this->db->bind(':parking', $data['parking']);
        $this->db->bind(':pet_policy', $data['pet_policy']);
        $this->db->bind(':laundry', $data['laundry']);
        $this->db->bind(':description', $data['description']);

        return $this->db->execute();
    }

    // Delete property
    public function deleteProperty($id)
    {
        $this->db->query('DELETE FROM properties WHERE id = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    // Get properties by landlord ID (alternative method name)
    public function getPropertiesByLandlordId($landlordId)
    {
        return $this->getPropertiesByLandlord($landlordId);
    }

    // Count properties by landlord
    public function countPropertiesByLandlord($landlordId)
    {
        $this->db->query('SELECT COUNT(*) as count FROM properties WHERE landlord_id = :landlord_id');
        $this->db->bind(':landlord_id', $landlordId);

        $result = $this->db->single();
        return $result ? $result->count : 0;
    }

    // Get properties by status
    public function getPropertiesByStatus($landlordId, $status)
    {
        $this->db->query('SELECT * FROM properties WHERE landlord_id = :landlord_id AND status = :status ORDER BY created_at DESC');
        $this->db->bind(':landlord_id', $landlordId);
        $this->db->bind(':status', $status);

        return $this->db->resultSet();
    }

    // Get properties by type
    public function getPropertiesByType($landlordId, $type)
    {
        $this->db->query('SELECT * FROM properties WHERE landlord_id = :landlord_id AND property_type = :property_type ORDER BY created_at DESC');
        $this->db->bind(':landlord_id', $landlordId);
        $this->db->bind(':property_type', $type);

        return $this->db->resultSet();
    }

    // NEW: Get properties by listing type
    public function getPropertiesByListingType($landlordId, $listingType)
    {
        $this->db->query('SELECT * FROM properties WHERE landlord_id = :landlord_id AND listing_type = :listing_type ORDER BY created_at DESC');
        $this->db->bind(':landlord_id', $landlordId);
        $this->db->bind(':listing_type', $listingType);

        return $this->db->resultSet();
    }

    // NEW: Get rental properties only
    public function getRentalProperties($landlordId)
    {
        return $this->getPropertiesByListingType($landlordId, 'rent');
    }

    // NEW: Get maintenance properties only
    public function getMaintenanceProperties($landlordId)
    {
        return $this->getPropertiesByListingType($landlordId, 'maintenance');
    }

    // Search properties
    public function searchProperties($landlordId, $searchTerm)
    {
        $this->db->query('SELECT * FROM properties WHERE landlord_id = :landlord_id AND (address LIKE :search OR description LIKE :search) ORDER BY created_at DESC');
        $this->db->bind(':landlord_id', $landlordId);
        $this->db->bind(':search', '%' . $searchTerm . '%');

        return $this->db->resultSet();
    }

    // Get recent properties
    public function getRecentProperties($landlordId, $limit = 5)
    {
        $this->db->query('SELECT * FROM properties WHERE landlord_id = :landlord_id ORDER BY created_at DESC LIMIT :limit');
        $this->db->bind(':landlord_id', $landlordId);
        $this->db->bind(':limit', $limit);

        return $this->db->resultSet();
    }

    // Update property status
    public function updateStatus($propertyId, $status)
    {
        $this->db->query('UPDATE properties SET status = :status WHERE id = :id');
        $this->db->bind(':id', $propertyId);
        $this->db->bind(':status', $status);

        return $this->db->execute();
    }

    // Get property statistics
    public function getPropertyStats($landlordId)
    {
        $this->db->query('SELECT 
                         COUNT(*) as total_properties,
                         COUNT(CASE WHEN listing_type = "rent" THEN 1 END) as rental_properties,
                         COUNT(CASE WHEN listing_type = "maintenance" THEN 1 END) as maintenance_properties,
                         COUNT(CASE WHEN status = "occupied" THEN 1 END) as occupied,
                         COUNT(CASE WHEN status = "vacant" THEN 1 END) as vacant,
                         COUNT(CASE WHEN status = "maintenance" THEN 1 END) as maintenance,
                         COUNT(CASE WHEN status = "maintenance_only" THEN 1 END) as maintenance_only,
                         AVG(CASE WHEN listing_type = "rent" THEN rent ELSE NULL END) as average_rent,
                         SUM(CASE WHEN status = "occupied" AND listing_type = "rent" THEN rent ELSE 0 END) as monthly_revenue
                         FROM properties WHERE landlord_id = :landlord_id');
        $this->db->bind(':landlord_id', $landlordId);

        return $this->db->single();
    }

    // NEW: Count properties by listing type
    public function countPropertiesByListingType($landlordId, $listingType)
    {
        $this->db->query('SELECT COUNT(*) as count FROM properties WHERE landlord_id = :landlord_id AND listing_type = :listing_type');
        $this->db->bind(':landlord_id', $landlordId);
        $this->db->bind(':listing_type', $listingType);

        $result = $this->db->single();
        return $result ? $result->count : 0;
    }

    // Alias for getPropertyStats (used by Landlord controller)
    public function getPropertyStatsByLandlord($landlordId)
    {
        return $this->getPropertyStats($landlordId);
    }

    // Alias for updateStatus (used by various controllers)
    public function updatePropertyStatus($propertyId, $status)
    {
        return $this->updateStatus($propertyId, $status);
    }

    // Get all properties (for admin)
    public function getAllProperties()
    {
        $this->db->query('SELECT p.*, u.name as landlord_name, u.email as landlord_email
                         FROM properties p
                         LEFT JOIN users u ON p.landlord_id = u.id
                         ORDER BY p.created_at DESC');
        return $this->db->resultSet();
    }
}
