<?php
class M_Users
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // ==========================================
    // REGISTRATION METHODS
    // ==========================================

    // Register regular user (tenant, landlord, admin)
    public function register($data)
    {
        $this->db->query('INSERT INTO users (name, email, password, user_type, account_status, terms_accepted_at, terms_version) 
                          VALUES (:name, :email, :password, :user_type, :account_status, :terms_accepted_at, :terms_version)');

        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':user_type', $data['user_type']);
        $this->db->bind(':account_status', 'active');
        $this->db->bind(':terms_accepted_at', $data['accept_terms'] ? date('Y-m-d H:i:s') : null);
        $this->db->bind(':terms_version', '1.0');

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Register Property Manager with employee ID (two tables)
    public function registerPM($data)
    {
        // Step 1: Insert into users table
        $this->db->query('INSERT INTO users (name, email, password, user_type, account_status, terms_accepted_at, terms_version) 
                          VALUES (:name, :email, :password, :user_type, :account_status, :terms_accepted_at, :terms_version)');

        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':user_type', 'property_manager');
        $this->db->bind(':account_status', 'pending');
        $this->db->bind(':terms_accepted_at', $data['accept_terms'] ? date('Y-m-d H:i:s') : null);
        $this->db->bind(':terms_version', '1.0');

        if (!$this->db->execute()) {
            return false;
        }

        // Get the newly created user ID
        $user_id = $this->db->lastInsertId();

        // Step 2: Insert into property_manager table
        $this->db->query('INSERT INTO property_manager (user_id, employee_id_document, employee_id_filename, 
                          employee_id_filetype, employee_id_filesize, approval_status) 
                          VALUES (:user_id, :employee_id_document, :employee_id_filename, 
                          :employee_id_filetype, :employee_id_filesize, :approval_status)');

        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':employee_id_document', $data['employee_id_data']);
        $this->db->bind(':employee_id_filename', $data['employee_id_filename']);
        $this->db->bind(':employee_id_filetype', $data['employee_id_filetype']);
        $this->db->bind(':employee_id_filesize', $data['employee_id_filesize']);
        $this->db->bind(':approval_status', 'pending');

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // ==========================================
    // LOGIN & AUTHENTICATION
    // ==========================================

    // Find user by email
    public function findUserByEmail($email)
    {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if ($this->db->rowcount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Login user
    public function login($email, $password)
    {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if (!$row) {
            return false; // User not found
        }

        $hashed_password = $row->password;

        if (password_verify($password, $hashed_password)) {
            // Check if PM account is approved
            if ($row->user_type === 'property_manager') {
                if ($row->account_status === 'pending') {
                    return 'pending';
                }
                if ($row->account_status === 'rejected') {
                    return 'rejected';
                }
                if ($row->account_status === 'suspended') {
                    return 'suspended';
                }
            }
            return $row; // Login successful
        } else {
            return false; // Wrong password
        }
    }

    // ==========================================
    // USER PROFILE METHODS
    // ==========================================

    // Get user by ID
    public function getUserById($id)
    {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Update user profile
    public function updateUser($data)
    {
        if (isset($data['password'])) {
            $this->db->query('UPDATE users 
                              SET name = :name, email = :email, password = :password 
                              WHERE id = :id');
            $this->db->bind(':password', $data['password']);
        } else {
            $this->db->query('UPDATE users 
                              SET name = :name, email = :email 
                              WHERE id = :id');
        }

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // ==========================================
    // PROPERTY MANAGER SPECIFIC METHODS
    // ==========================================

    // Get pending Property Managers for admin approval (JOIN both tables)
    public function getPendingPMs()
    {
        $this->db->query('SELECT u.id, u.name, u.email, u.created_at,
                                 pm.approval_status
                          FROM users u
                          INNER JOIN property_manager pm ON u.id = pm.user_id
                          WHERE u.user_type = :user_type AND pm.approval_status = :status
                          ORDER BY u.created_at DESC');

        $this->db->bind(':user_type', 'property_manager');
        $this->db->bind(':status', 'pending');

        return $this->db->resultSet();
    }

    // Get ALL Property Managers (pending, approved, rejected)
    public function getAllPropertyManagers()
    {
        $this->db->query('SELECT u.id, u.name, u.email, u.account_status, u.created_at,
                                 pm.approval_status, pm.phone, pm.approved_at
                          FROM users u
                          INNER JOIN property_manager pm ON u.id = pm.user_id
                          WHERE u.user_type = :user_type
                          ORDER BY
                              CASE
                                  WHEN pm.approval_status = "pending" THEN 1
                                  WHEN pm.approval_status = "approved" THEN 2
                                  WHEN pm.approval_status = "rejected" THEN 3
                                  ELSE 4
                              END,
                              u.created_at DESC');

        $this->db->bind(':user_type', 'property_manager');

        return $this->db->resultSet();
    }

    // Get managers by status
    public function getManagersByStatus($status)
    {
        $this->db->query('SELECT u.id, u.name, u.email, u.created_at,
                                 pm.employee_id_filename, pm.phone
                          FROM users u
                          INNER JOIN property_manager pm ON u.id = pm.user_id
                          WHERE u.user_type = :user_type AND pm.approval_status = :status 
                          ORDER BY u.created_at DESC');

        $this->db->bind(':user_type', 'property_manager');
        $this->db->bind(':status', $status);

        return $this->db->resultSet();
    }

    // Get manager counts
    public function getManagerCounts()
    {
        $this->db->query('SELECT 
                              COUNT(*) as total,
                              SUM(CASE WHEN pm.approval_status = "pending" THEN 1 ELSE 0 END) as pending,
                              SUM(CASE WHEN pm.approval_status = "approved" THEN 1 ELSE 0 END) as approved,
                              SUM(CASE WHEN pm.approval_status = "rejected" THEN 1 ELSE 0 END) as rejected
                          FROM users u
                          INNER JOIN property_manager pm ON u.id = pm.user_id
                          WHERE u.user_type = :user_type');

        $this->db->bind(':user_type', 'property_manager');

        return $this->db->single();
    }

    // Get employee ID document by user ID
    public function getEmployeeIdDocument($userId)
    {
        $this->db->query('SELECT employee_id_document, employee_id_filename, employee_id_filetype 
                          FROM property_manager 
                          WHERE user_id = :user_id');

        $this->db->bind(':user_id', $userId);

        return $this->db->single();
    }

    // Approve Property Manager account (update both tables)
    public function approvePM($userId)
    {
        // Update users table
        $this->db->query('UPDATE users 
                          SET account_status = :status 
                          WHERE id = :id AND user_type = :user_type');

        $this->db->bind(':status', 'active');
        $this->db->bind(':id', $userId);
        $this->db->bind(':user_type', 'property_manager');

        if (!$this->db->execute()) {
            return false;
        }

        // Update property_manager table
        $this->db->query('UPDATE property_manager 
                          SET approval_status = :approval_status, approved_at = NOW() 
                          WHERE user_id = :user_id');

        $this->db->bind(':approval_status', 'approved');
        $this->db->bind(':user_id', $userId);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Reject Property Manager account (update both tables)
    public function rejectPM($userId, $adminId)
    {
        // Update users table
        $this->db->query('UPDATE users 
                          SET account_status = :status 
                          WHERE id = :id');

        $this->db->bind(':status', 'rejected');
        $this->db->bind(':id', $userId);

        if (!$this->db->execute()) {
            return false;
        }

        // Update property_manager table
        $this->db->query('UPDATE property_manager 
                          SET approval_status = :approval_status, approved_by = :admin_id, approved_at = NOW() 
                          WHERE user_id = :user_id');

        $this->db->bind(':approval_status', 'rejected');
        $this->db->bind(':admin_id', $adminId);
        $this->db->bind(':user_id', $userId);

        return $this->db->execute();
    }

    // Remove/delete a property manager (cascades automatically due to FK)
    public function removePropertyManager($userId)
    {
        $this->db->query('DELETE FROM users 
                          WHERE id = :id AND user_type = :user_type');

        $this->db->bind(':id', $userId);
        $this->db->bind(':user_type', 'property_manager');

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Update PM phone number
    public function updatePMPhone($userId, $phone)
    {
        $this->db->query('UPDATE property_manager 
                          SET phone = :phone 
                          WHERE user_id = :user_id');

        $this->db->bind(':user_id', $userId);
        $this->db->bind(':phone', $phone);

        return $this->db->execute();
    }
}
