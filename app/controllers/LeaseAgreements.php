<?php

/*
    LEASE AGREEMENTS CONTROLLER
    Handles rental lease agreement operations
*/

class LeaseAgreements extends Controller
{
    private $leaseModel;
    private $bookingModel;
    private $notificationModel;

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->leaseModel = $this->model('M_LeaseAgreements');
        $this->bookingModel = $this->model('M_Bookings');
        $this->notificationModel = $this->model('M_Notifications');
    }

    // View lease details
    public function details($id)
    {
        $lease = $this->leaseModel->getLeaseById($id);

        if (!$lease) {
            flash('lease_message', 'Lease agreement not found', 'alert alert-danger');
            if (isPropertyManager()) {
                redirect('manager/leases');
            } else if (isTenant()) {
                redirect('tenant/agreements');
            } else {
                redirect('users/login');
            }
            return;
        }

        // Check if user has permission to view this lease
        $has_permission = false;

        if (isTenant() && $lease->tenant_id == $_SESSION['user_id']) {
            $has_permission = true;
        } else if (isLandlord() && $lease->landlord_id == $_SESSION['user_id']) {
            $has_permission = true;
        } else if (isPropertyManager()) {
            // Check if this PM is assigned to the property
            $propertyModel = $this->model('M_Properties');
            $property = $propertyModel->getPropertyById($lease->property_id);
            if ($property && $property->manager_id == $_SESSION['user_id']) {
                $has_permission = true;
            }
        }

        if (!$has_permission) {
            flash('lease_message', 'Unauthorized access', 'alert alert-danger');
            if (isPropertyManager()) {
                redirect('manager/leases');
            } else if (isTenant()) {
                redirect('tenant/agreements');
            } else {
                redirect('users/login');
            }
            return;
        }

        $data = [
            'lease' => $lease
        ];

        if (isTenant()) {
            $this->view('tenant/v_lease_details', $data);
        } else if (isLandlord()) {
            $this->view('landlord/v_lease_details', $data);
        } else if (isPropertyManager()) {
            $this->view('manager/v_lease_details', $data);
        }
    }

    // Sign lease by tenant
    public function signTenant($id)
    {
        if (!isTenant()) {
            flash('lease_message', 'Unauthorized access', 'alert alert-danger');
            redirect('users/login');
        }

        $lease = $this->leaseModel->getLeaseById($id);

        if (!$lease || $lease->tenant_id != $_SESSION['user_id']) {
            flash('lease_message', 'Unauthorized access', 'alert alert-danger');
            redirect('tenant/agreements');
        }

        if ($lease->signed_by_tenant) {
            flash('lease_message', 'You have already signed this lease', 'alert alert-info');
            redirect('tenant/agreements');
        }

        if ($this->leaseModel->signLeaseByTenant($id)) {
            // Notify landlord that tenant has signed
            $this->notificationModel->createNotification([
                'user_id' => $lease->landlord_id,
                'type' => 'lease',
                'title' => 'Lease Agreement Signed by Tenant',
                'message' => 'The tenant has signed the lease agreement for property at "' . substr($lease->property_address, 0, 50) . '..."',
                'link' => 'leaseagreements/details/' . $id
            ]);

            // Check if both parties have signed
            $lease = $this->leaseModel->getLeaseById($id);
            if ($lease->signed_by_landlord) {
                // Activate the lease and booking
                $this->leaseModel->updateLeaseStatus($id, 'active');
                $this->bookingModel->activateBooking($lease->booking_id);

                // Notify both parties that lease is now active
                $this->notificationModel->createNotification([
                    'user_id' => $lease->tenant_id,
                    'type' => 'lease',
                    'title' => 'Lease Agreement Active',
                    'message' => 'Your lease agreement for property at "' . substr($lease->property_address, 0, 50) . '..." is now active!',
                    'link' => 'tenant/agreements'
                ]);

                $this->notificationModel->createNotification([
                    'user_id' => $lease->landlord_id,
                    'type' => 'lease',
                    'title' => 'Lease Agreement Active',
                    'message' => 'Lease agreement for property at "' . substr($lease->property_address, 0, 50) . '..." is now active!',
                    'link' => 'landlord/bookings'
                ]);
            } else {
                $this->leaseModel->updateLeaseStatus($id, 'pending_signatures');
            }

            flash('lease_message', 'Lease agreement signed successfully', 'alert alert-success');
        } else {
            flash('lease_message', 'Failed to sign lease agreement', 'alert alert-danger');
        }

        redirect('tenant/agreements');
    }

    // Sign lease by landlord
    public function signLandlord($id)
    {
        if (!isLandlord()) {
            flash('lease_message', 'Unauthorized access', 'alert alert-danger');
            redirect('users/login');
        }

        $lease = $this->leaseModel->getLeaseById($id);

        if (!$lease || $lease->landlord_id != $_SESSION['user_id']) {
            flash('lease_message', 'Unauthorized access', 'alert alert-danger');
            redirect('landlord/dashboard');
        }

        if ($lease->signed_by_landlord) {
            flash('lease_message', 'You have already signed this lease', 'alert alert-info');
            redirect('landlord/bookings');
        }

        if ($this->leaseModel->signLeaseByLandlord($id)) {
            // Notify tenant that landlord has signed
            $this->notificationModel->createNotification([
                'user_id' => $lease->tenant_id,
                'type' => 'lease',
                'title' => 'Lease Agreement Signed by Landlord',
                'message' => 'The landlord has signed the lease agreement for property at "' . substr($lease->property_address, 0, 50) . '..."',
                'link' => 'leaseagreements/details/' . $id
            ]);

            // Check if both parties have signed
            $lease = $this->leaseModel->getLeaseById($id);
            if ($lease->signed_by_tenant) {
                // Activate the lease and booking
                $this->leaseModel->updateLeaseStatus($id, 'active');
                $this->bookingModel->activateBooking($lease->booking_id);

                // Notify both parties that lease is now active
                $this->notificationModel->createNotification([
                    'user_id' => $lease->tenant_id,
                    'type' => 'lease',
                    'title' => 'Lease Agreement Active',
                    'message' => 'Your lease agreement for property at "' . substr($lease->property_address, 0, 50) . '..." is now active!',
                    'link' => 'tenant/agreements'
                ]);

                $this->notificationModel->createNotification([
                    'user_id' => $lease->landlord_id,
                    'type' => 'lease',
                    'title' => 'Lease Agreement Active',
                    'message' => 'Lease agreement for property at "' . substr($lease->property_address, 0, 50) . '..." is now active!',
                    'link' => 'landlord/bookings'
                ]);
            } else {
                $this->leaseModel->updateLeaseStatus($id, 'pending_signatures');
            }

            flash('lease_message', 'Lease agreement signed successfully', 'alert alert-success');
        } else {
            flash('lease_message', 'Failed to sign lease agreement', 'alert alert-danger');
        }

        redirect('landlord/bookings');
    }

    // Terminate lease
    public function terminate($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $lease = $this->leaseModel->getLeaseById($id);

            if (!$lease) {
                flash('lease_message', 'Lease agreement not found', 'alert alert-danger');
                redirect('tenant/dashboard');
            }

            // Check if user has permission
            if ($lease->tenant_id != $_SESSION['user_id'] && $lease->landlord_id != $_SESSION['user_id']) {
                flash('lease_message', 'Unauthorized access', 'alert alert-danger');
                redirect('tenant/dashboard');
            }

            $termination_reason = trim($_POST['termination_reason']);
            $termination_date = trim($_POST['termination_date']);

            if ($this->leaseModel->terminateLease($id, $termination_reason, $termination_date)) {
                // Complete the booking
                $this->bookingModel->completeBooking($lease->booking_id);

                // Notify the other party about lease termination
                $initiator = $_SESSION['user_id'];
                $other_party_id = ($lease->tenant_id == $initiator) ? $lease->landlord_id : $lease->tenant_id;
                $initiator_role = ($lease->tenant_id == $initiator) ? 'tenant' : 'landlord';

                $this->notificationModel->createNotification([
                    'user_id' => $other_party_id,
                    'type' => 'lease',
                    'title' => 'Lease Agreement Terminated',
                    'message' => 'The lease agreement for property at "' . substr($lease->property_address, 0, 50) . '..." has been terminated by the ' . $initiator_role . '.',
                    'link' => 'leaseagreements/details/' . $id
                ]);

                flash('lease_message', 'Lease agreement terminated successfully', 'alert alert-success');
            } else {
                flash('lease_message', 'Failed to terminate lease agreement', 'alert alert-danger');
            }

            if (isTenant()) {
                redirect('tenant/agreements');
            } else {
                redirect('landlord/bookings');
            }
        }
    }
}
