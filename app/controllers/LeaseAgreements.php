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
    public function view($id)
    {
        $lease = $this->leaseModel->getLeaseById($id);

        if (!$lease) {
            flash('lease_message', 'Lease agreement not found', 'alert alert-danger');
            redirect('tenant/dashboard');
        }

        // Check if user has permission to view this lease
        if ($lease->tenant_id != $_SESSION['user_id'] && $lease->landlord_id != $_SESSION['user_id']) {
            flash('lease_message', 'Unauthorized access', 'alert alert-danger');
            redirect('tenant/dashboard');
        }

        $data = [
            'lease' => $lease
        ];

        if (isTenant()) {
            $this->view('tenant/v_lease_details', $data);
        } else if (isLandlord()) {
            $this->view('landlord/v_lease_details', $data);
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
            // Check if both parties have signed
            $lease = $this->leaseModel->getLeaseById($id);
            if ($lease->signed_by_landlord) {
                // Activate the lease and booking
                $this->leaseModel->updateLeaseStatus($id, 'active');
                $this->bookingModel->activateBooking($lease->booking_id);
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
            // Check if both parties have signed
            $lease = $this->leaseModel->getLeaseById($id);
            if ($lease->signed_by_tenant) {
                // Activate the lease and booking
                $this->leaseModel->updateLeaseStatus($id, 'active');
                $this->bookingModel->activateBooking($lease->booking_id);
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
