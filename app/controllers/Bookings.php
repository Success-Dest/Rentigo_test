<?php

/*
    BOOKINGS CONTROLLER
    Handles property booking/reservation operations for tenants and landlords
*/

class Bookings extends Controller
{
    private $bookingModel;
    private $propertyModel;
    private $notificationModel;
    private $leaseModel;

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->bookingModel = $this->model('M_Bookings');
        $this->propertyModel = $this->model('M_Properties');
        $this->notificationModel = $this->model('M_Notifications');
        $this->leaseModel = $this->model('M_LeaseAgreements');
    }

    // Create a new booking (Tenant)
    public function create($property_id)
    {
        if (!isTenant()) {
            flash('booking_message', 'Only tenants can book properties', 'alert alert-danger');
            redirect('tenant/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Get property details
            $property = $this->propertyModel->getPropertyById($property_id);

            if (!$property) {
                flash('booking_message', 'Property not found', 'alert alert-danger');
                redirect('tenantproperties/index');
            }

            // Check if property is available
            if ($property->status != 'available') {
                flash('booking_message', 'This property is not available for booking', 'alert alert-danger');
                redirect('tenantproperties/details/' . $property_id);
            }

            // Check if tenant already has an active booking
            $activeBooking = $this->bookingModel->getActiveBookingByTenant($_SESSION['user_id']);
            if ($activeBooking) {
                flash('booking_message', 'You already have an active booking', 'alert alert-danger');
                redirect('tenant/bookings');
            }

            // Validate dates
            $move_in_date = trim($_POST['move_in_date']);
            $move_out_date = trim($_POST['move_out_date']);
            $notes = trim($_POST['notes']);

            if (strtotime($move_in_date) < strtotime('today')) {
                flash('booking_message', 'Move-in date cannot be in the past', 'alert alert-danger');
                redirect('tenantproperties/details/' . $property_id);
            }

            if (strtotime($move_out_date) <= strtotime($move_in_date)) {
                flash('booking_message', 'Move-out date must be after move-in date', 'alert alert-danger');
                redirect('tenantproperties/details/' . $property_id);
            }

            // Check for date conflicts
            if ($this->bookingModel->checkDateConflict($property_id, $move_in_date, $move_out_date)) {
                flash('booking_message', 'Selected dates conflict with an existing booking', 'alert alert-danger');
                redirect('tenantproperties/details/' . $property_id);
            }

            // Calculate total amount
            $monthly_rent = $property->rent;
            $deposit_amount = $property->deposit ?? $monthly_rent;
            $total_amount = $deposit_amount + $monthly_rent;

            $data = [
                'tenant_id' => $_SESSION['user_id'],
                'property_id' => $property_id,
                'landlord_id' => $property->landlord_id,
                'move_in_date' => $move_in_date,
                'move_out_date' => $move_out_date,
                'monthly_rent' => $monthly_rent,
                'deposit_amount' => $deposit_amount,
                'total_amount' => $total_amount,
                'status' => 'pending',
                'notes' => $notes
            ];

            $booking_id = $this->bookingModel->createBooking($data);

            if ($booking_id) {
                // Send notification to property manager (if assigned)
                if ($property->manager_id) {
                    $this->notificationModel->createNotification([
                        'user_id' => $property->manager_id,
                        'type' => 'booking',
                        'title' => 'New Booking Request',
                        'message' => $_SESSION['user_name'] . ' has requested to book property at "' . substr($property->address, 0, 50) . '..."',
                        'link' => 'bookings/details/' . $booking_id
                    ]);
                }

                flash('booking_message', 'Booking request submitted successfully! Waiting for Property Manager approval.', 'alert alert-success');
                redirect('tenant/bookings');
            } else {
                flash('booking_message', 'Something went wrong. Please try again.', 'alert alert-danger');
                redirect('tenantproperties/details/' . $property_id);
            }
        } else {
            redirect('tenantproperties/index');
        }
    }

    // View booking details
    public function details($id)
    {
        $booking = $this->bookingModel->getBookingById($id);

        if (!$booking) {
            flash('booking_message', 'Booking not found', 'alert alert-danger');
            if (isPropertyManager()) {
                redirect('manager/bookings');
            } else if (isTenant()) {
                redirect('tenant/bookings');
            } else {
                redirect('users/login');
            }
            return;
        }

        // Check if user has permission to view this booking
        $has_permission = false;

        if (isTenant() && $booking->tenant_id == $_SESSION['user_id']) {
            $has_permission = true;
        } else if (isLandlord() && $booking->landlord_id == $_SESSION['user_id']) {
            $has_permission = true;
        } else if (isPropertyManager()) {
            // Check if this PM is assigned to the property
            $property = $this->propertyModel->getPropertyById($booking->property_id);
            if ($property && $property->manager_id == $_SESSION['user_id']) {
                $has_permission = true;
            }
        }

        if (!$has_permission) {
            flash('booking_message', 'Unauthorized access', 'alert alert-danger');
            if (isPropertyManager()) {
                redirect('manager/bookings');
            } else if (isTenant()) {
                redirect('tenant/bookings');
            } else {
                redirect('users/login');
            }
            return;
        }

        $data = [
            'booking' => $booking
        ];

        // Load appropriate view based on user type
        if (isTenant()) {
            $this->view('tenant/v_booking_details', $data);
        } else if (isLandlord()) {
            $this->view('landlord/v_booking_details', $data);
        } else if (isPropertyManager()) {
            $this->view('manager/v_booking_details', $data);
        }
    }

    // Approve booking (Property Manager)
    public function approve($id)
    {
        if (!isPropertyManager()) {
            flash('booking_message', 'Only Property Managers can approve bookings', 'alert alert-danger');
            redirect('users/login');
        }

        $booking = $this->bookingModel->getBookingById($id);

        if (!$booking) {
            flash('booking_message', 'Booking not found', 'alert alert-danger');
            redirect('manager/dashboard');
        }

        // Check if this property is assigned to this PM
        $property = $this->propertyModel->getPropertyById($booking->property_id);
        if ($property->manager_id != $_SESSION['user_id']) {
            flash('booking_message', 'Unauthorized - This property is not assigned to you', 'alert alert-danger');
            redirect('manager/dashboard');
        }

        if ($this->bookingModel->updateBookingStatus($id, 'approved')) {
            // Update property status
            $this->propertyModel->updatePropertyStatus($booking->property_id, 'occupied');

            // Create notification for tenant
            $this->notificationModel->notifyBookingApproved(
                $booking->tenant_id,
                $booking->address,
                $id
            );

            // Create lease agreement
            $lease_data = [
                'tenant_id' => $booking->tenant_id,
                'landlord_id' => $booking->landlord_id,
                'property_id' => $booking->property_id,
                'booking_id' => $id,
                'start_date' => $booking->move_in_date,
                'end_date' => $booking->move_out_date,
                'monthly_rent' => $booking->monthly_rent,
                'deposit_amount' => $booking->deposit_amount,
                'terms_and_conditions' => 'Standard lease terms and conditions apply.',
                'status' => 'draft',
                'lease_duration_months' => $this->calculateMonthsDifference($booking->move_in_date, $booking->move_out_date)
            ];

            $this->leaseModel->createLeaseAgreement($lease_data);

            // Create scheduled monthly payments
            $paymentModel = $this->model('M_Payments');
            $payments_created = $paymentModel->createScheduledPayments(
                $id,
                $booking->tenant_id,
                $booking->landlord_id,
                $booking->property_id,
                $booking->monthly_rent, // Store base rent (what landlord receives)
                $booking->move_in_date,
                $booking->move_out_date
            );

            flash('booking_message', 'Booking approved successfully! ' . $payments_created . ' payment(s) scheduled.', 'alert alert-success');
        } else {
            flash('booking_message', 'Failed to approve booking', 'alert alert-danger');
        }

        redirect('manager/bookings');
    }

    // Reject booking (Property Manager)
    public function reject($id)
    {
        if (!isPropertyManager()) {
            flash('booking_message', 'Only Property Managers can reject bookings', 'alert alert-danger');
            redirect('users/login');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $booking = $this->bookingModel->getBookingById($id);

            if (!$booking) {
                flash('booking_message', 'Booking not found', 'alert alert-danger');
                redirect('manager/dashboard');
                return;
            }

            // Check if this property is assigned to this PM
            $property = $this->propertyModel->getPropertyById($booking->property_id);
            if ($property->manager_id != $_SESSION['user_id']) {
                flash('booking_message', 'Unauthorized - This property is not assigned to you', 'alert alert-danger');
                redirect('manager/dashboard');
                return;
            }

            $rejection_reason = trim($_POST['rejection_reason']);

            if ($this->bookingModel->updateBookingStatus($id, 'rejected', $rejection_reason)) {
                // Create notification for tenant
                $this->notificationModel->notifyBookingRejected(
                    $booking->tenant_id,
                    $booking->address,
                    $rejection_reason
                );

                flash('booking_message', 'Booking rejected', 'alert alert-success');
            } else {
                flash('booking_message', 'Failed to reject booking', 'alert alert-danger');
            }
        }

        redirect('manager/bookings');
    }

    // Cancel booking (Tenant)
    public function cancel($id)
    {
        if (!isTenant()) {
            flash('booking_message', 'Unauthorized access', 'alert alert-danger');
            redirect('users/login');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $booking = $this->bookingModel->getBookingById($id);

            if (!$booking || $booking->tenant_id != $_SESSION['user_id']) {
                flash('booking_message', 'Unauthorized access', 'alert alert-danger');
                redirect('tenant/dashboard');
            }

            if ($booking->status != 'pending') {
                flash('booking_message', 'Can only cancel pending bookings', 'alert alert-danger');
                redirect('tenant/bookings');
            }

            $cancellation_reason = trim($_POST['cancellation_reason']);

            if ($this->bookingModel->cancelBooking($id, $cancellation_reason)) {
                flash('booking_message', 'Booking cancelled successfully', 'alert alert-success');
            } else {
                flash('booking_message', 'Failed to cancel booking', 'alert alert-danger');
            }
        }

        redirect('tenant/bookings');
    }

    // Helper function to calculate months difference
    private function calculateMonthsDifference($start_date, $end_date)
    {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        return ($interval->y * 12) + $interval->m;
    }
}
