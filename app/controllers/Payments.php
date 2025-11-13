<?php

/*
    PAYMENTS CONTROLLER
    Handles rent payment operations (simulated - no real payment gateway)
*/

class Payments extends Controller
{
    private $paymentModel;
    private $bookingModel;
    private $notificationModel;
    private $propertyModel;

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->paymentModel = $this->model('M_Payments');
        $this->bookingModel = $this->model('M_Bookings');
        $this->notificationModel = $this->model('M_Notifications');
        $this->propertyModel = $this->model('M_Properties');
    }

    // View payment details
    public function details($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);

        if (!$payment) {
            flash('payment_message', 'Payment not found', 'alert alert-danger');
            redirect('tenant/dashboard');
        }

        // Check if user has permission to view this payment
        if ($payment->tenant_id != $_SESSION['user_id'] && $payment->landlord_id != $_SESSION['user_id']) {
            flash('payment_message', 'Unauthorized access', 'alert alert-danger');
            redirect('tenant/dashboard');
        }

        $data = [
            'payment' => $payment
        ];

        if (isTenant()) {
            $this->view('tenant/v_payment_details', $data);
        } else if (isLandlord()) {
            $this->view('landlord/v_payment_details', $data);
        }
    }

    // Process payment (Tenant) - SIMULATED
    public function process($payment_id = null)
    {
        if (!isTenant()) {
            flash('payment_message', 'Only tenants can make payments', 'alert alert-danger');
            redirect('users/login');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $payment_id = trim($_POST['payment_id']);
            $payment_method = trim($_POST['payment_method']);

            $payment = $this->paymentModel->getPaymentById($payment_id);

            if (!$payment) {
                flash('payment_message', 'Payment not found', 'alert alert-danger');
                redirect('tenant/pay_rent');
            }

            if ($payment->tenant_id != $_SESSION['user_id']) {
                flash('payment_message', 'Unauthorized access', 'alert alert-danger');
                redirect('tenant/pay_rent');
            }

            if ($payment->status != 'pending') {
                flash('payment_message', 'This payment has already been processed', 'alert alert-info');
                redirect('tenant/pay_rent');
            }

            // SIMULATE PAYMENT PROCESSING
            // In a real system, this would integrate with a payment gateway
            // For simulation, we'll generate a transaction ID and mark as completed

            $transaction_id = 'TXN' . time() . rand(1000, 9999);
            $payment_date = date('Y-m-d H:i:s');

            // Update payment record
            $this->paymentModel->updatePaymentStatus($payment_id, 'completed');

            // Update with transaction details
            $this->paymentModel->updatePaymentTransaction($payment_id, $transaction_id, $payment_method, $payment_date);

            // Create notification for landlord
            $this->notificationModel->notifyPaymentReceived(
                $payment->landlord_id,
                number_format($payment->amount, 2),
                $_SESSION['user_name'],
                $payment->property_address
            );

            flash('payment_message', 'Payment processed successfully! Transaction ID: ' . $transaction_id, 'alert alert-success');
            redirect('tenant/pay_rent');
        } else {
            redirect('tenant/pay_rent');
        }
    }

    // Create manual payment (for deposit or other charges)
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'tenant_id' => trim($_POST['tenant_id']),
                'landlord_id' => $_SESSION['user_id'],
                'property_id' => trim($_POST['property_id']),
                'booking_id' => trim($_POST['booking_id']),
                'amount' => trim($_POST['amount']),
                'payment_method' => 'pending',
                'transaction_id' => '',
                'status' => 'pending',
                'payment_date' => null,
                'due_date' => trim($_POST['due_date']),
                'notes' => trim($_POST['notes'])
            ];

            if ($this->paymentModel->createPayment($data)) {
                // Create notification for tenant
                $property = $this->propertyModel->getPropertyById($data['property_id']);
                $this->notificationModel->notifyPaymentDue(
                    $data['tenant_id'],
                    number_format($data['amount'], 2),
                    $data['due_date'],
                    $property->address
                );

                flash('payment_message', 'Payment request created successfully', 'alert alert-success');
            } else {
                flash('payment_message', 'Failed to create payment request', 'alert alert-danger');
            }

            redirect('landlord/payment_history');
        } else {
            redirect('landlord/dashboard');
        }
    }

    // Get payment receipt
    public function receipt($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);

        if (!$payment) {
            flash('payment_message', 'Payment not found', 'alert alert-danger');
            redirect('tenant/dashboard');
        }

        // Check if user has permission to view this receipt
        if ($payment->tenant_id != $_SESSION['user_id'] && $payment->landlord_id != $_SESSION['user_id']) {
            flash('payment_message', 'Unauthorized access', 'alert alert-danger');
            redirect('tenant/dashboard');
        }

        if ($payment->status != 'completed') {
            flash('payment_message', 'Receipt is only available for completed payments', 'alert alert-info');
            redirect('tenant/pay_rent');
        }

        $data = [
            'payment' => $payment
        ];

        $this->view('tenant/v_payment_receipt', $data);
    }

    // Create scheduled payments for active lease
    public function createScheduledPayments($booking_id)
    {
        if (!isLandlord()) {
            flash('payment_message', 'Unauthorized access', 'alert alert-danger');
            redirect('users/login');
        }

        $booking = $this->bookingModel->getBookingById($booking_id);

        if (!$booking || $booking->landlord_id != $_SESSION['user_id']) {
            flash('payment_message', 'Unauthorized access', 'alert alert-danger');
            redirect('landlord/dashboard');
        }

        if ($booking->status != 'active') {
            flash('payment_message', 'Can only create scheduled payments for active bookings', 'alert alert-danger');
            redirect('landlord/bookings');
        }

        $count = $this->paymentModel->createScheduledPayments(
            $booking_id,
            $booking->tenant_id,
            $booking->landlord_id,
            $booking->property_id,
            $booking->monthly_rent,
            $booking->move_in_date,
            $booking->move_out_date
        );

        flash('payment_message', "Created $count scheduled payment(s) successfully", 'alert alert-success');
        redirect('landlord/payment_history');
    }
}
