# RENTIGO - QUICK START GUIDE
## How to Complete the View Updates

---

## ✅ WHAT'S ALREADY DONE (100%)

1. **All Backend Logic** - 7 models, 5 new controllers, 3 updated controllers
2. **Complete Database** - 14 tables with test data
3. **Security Implementation** - PDO, password hashing, input sanitization
4. **Payment System** - Simulated payment processing
5. **All Business Logic** - Bookings, leases, payments, notifications, messages, reviews

---

## 🔄 WHAT NEEDS TO BE DONE

**View files need to display dynamic data instead of placeholders**

Current Status:
- Backend passes data to views: ✅ DONE
- Views display the data: ❌ NEEDS UPDATE (50+ files)

---

## 📖 HOW TO UPDATE A VIEW FILE

### Step 1: Identify What Data the Controller Passes

Open the controller file and find the view method. Example from `Tenant.php`:

```php
public function pay_rent()
{
    $pendingPayments = $this->paymentModel->getPendingPaymentsByTenant($_SESSION['user_id']);
    $paymentHistory = $this->paymentModel->getPaymentsByTenant($_SESSION['user_id']);

    $data = [
        'pendingPayments' => $pendingPayments,
        'paymentHistory' => $paymentHistory
    ];

    $this->view('tenant/v_pay_rent', $data);
}
```

**Data available in view:**
- `$data['pendingPayments']` - Array of pending payment objects
- `$data['paymentHistory']` - Array of all payment objects

---

### Step 2: Update the View File

**BEFORE (Hardcoded):**
```html
<div class="rent-amount">Rs 20,000</div>
<div class="due-date">Due: February 1, 2024</div>
```

**AFTER (Dynamic):**
```php
<?php if (!empty($data['pendingPayments'])): ?>
    <?php foreach ($data['pendingPayments'] as $payment): ?>
        <div class="payment-card">
            <div class="rent-amount">
                LKR <?php echo number_format($payment->amount, 2); ?>
            </div>
            <div class="due-date">
                Due: <?php echo date('F d, Y', strtotime($payment->due_date)); ?>
            </div>
            <button onclick="payNow(<?php echo $payment->id; ?>)">Pay Now</button>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-state">
        <p>No pending payments</p>
    </div>
<?php endif; ?>
```

---

### Step 3: Common Patterns

#### **Display a List of Items:**
```php
<?php if (!empty($data['items'])): ?>
    <?php foreach ($data['items'] as $item): ?>
        <div class="item">
            <h4><?php echo htmlspecialchars($item->title); ?></h4>
            <p><?php echo htmlspecialchars($item->description); ?></p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-state">No items found</div>
<?php endif; ?>
```

#### **Display Statistics:**
```php
<?php if (isset($data['stats'])): ?>
    <div class="stat-card">
        <h3><?php echo $data['stats']->total ?? 0; ?></h3>
        <p>Total Items</p>
    </div>
<?php endif; ?>
```

#### **Format Dates:**
```php
<?php echo date('M d, Y', strtotime($item->created_at)); ?>
<?php echo date('F d, Y', strtotime($item->due_date)); ?>
```

#### **Format Currency:**
```php
LKR <?php echo number_format($payment->amount, 2); ?>
```

#### **Display Status Badges:**
```php
<?php
$statusClass = '';
switch($item->status) {
    case 'active': $statusClass = 'approved'; break;
    case 'pending': $statusClass = 'pending'; break;
    case 'rejected': $statusClass = 'rejected'; break;
}
?>
<span class="status-badge <?php echo $statusClass; ?>">
    <?php echo ucfirst($item->status); ?>
</span>
```

---

## 🎯 PRIORITY VIEW FILES TO UPDATE

Update these files in this order for maximum functionality:

### **HIGHEST PRIORITY (Update First):**

1. **`/app/views/tenant/v_pay_rent.php`**
   - Controller: `Tenant::pay_rent()`
   - Data: `pendingPayments`, `paymentHistory`, `totalPayments`, `overduePayments`
   - Critical Feature: Payment simulation

2. **`/app/views/tenant/v_agreements.php`**
   - Controller: `Tenant::agreements()`
   - Data: `leases`, `activeLease`, `leaseStats`
   - Critical Feature: Lease management

3. **`/app/views/tenant/v_dashboard.php`**
   - Controller: `Tenant::index()`
   - Data: `activeBooking`, `activeLease`, `pendingPayments`, `recentIssues`, `bookingStats`
   - Critical Feature: Main tenant view

4. **`/app/views/landlord/v_dashboard.php`**
   - Controller: `Landlord::dashboard()`
   - Data: `propertyStats`, `bookingStats`, `pendingBookings`, `totalIncome`, etc.
   - Critical Feature: Main landlord view

5. **`/app/views/landlord/v_payment_history.php`**
   - Controller: `Landlord::payment_history()`
   - Data: `payments`, `totalIncome`, `paymentStats`
   - Critical Feature: Income tracking

### **MEDIUM PRIORITY:**

6. **`/app/views/tenant/v_notifications.php`**
7. **`/app/views/tenant/v_my_reviews.php`**
8. **`/app/views/landlord/v_notifications.php`**
9. **`/app/views/landlord/v_feedback.php`**
10. **`/app/views/landlord/v_income.php`**

---

## 🛠️ EXAMPLE: Complete View Update

### File: `/app/views/tenant/v_notifications.php`

**Step 1:** Check controller (`Tenant::notifications()`)
```php
$notifications = $this->notificationModel->getNotificationsByUser($_SESSION['user_id']);
$unreadCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);
```

**Step 2:** Update view
```php
<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <div class="page-header">
        <h2>Notifications</h2>
        <p>You have <?php echo $data['unreadCount'] ?? 0; ?> unread notification(s)</p>
    </div>

    <?php flash('notification_message'); ?>

    <?php if (!empty($data['notifications'])): ?>
        <div class="notifications-list">
            <?php foreach ($data['notifications'] as $notif): ?>
                <div class="notification-card <?php echo $notif->is_read ? 'read' : 'unread'; ?>">
                    <div class="notif-icon">
                        <i class="fas fa-<?php echo $notif->type == 'payment' ? 'credit-card' : 'bell'; ?>"></i>
                    </div>
                    <div class="notif-content">
                        <h4><?php echo htmlspecialchars($notif->title); ?></h4>
                        <p><?php echo htmlspecialchars($notif->message); ?></p>
                        <span class="notif-time">
                            <?php echo date('M d, Y - H:i', strtotime($notif->created_at)); ?>
                        </span>
                    </div>
                    <div class="notif-actions">
                        <?php if (!$notif->is_read): ?>
                            <a href="<?php echo URLROOT; ?>/tenant/markNotificationRead/<?php echo $notif->id; ?>" class="btn btn-sm">
                                Mark Read
                            </a>
                        <?php endif; ?>
                        <?php if ($notif->link): ?>
                            <a href="<?php echo $notif->link; ?>" class="btn btn-primary btn-sm">View</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="notification-actions">
            <a href="<?php echo URLROOT; ?>/tenant/markAllNotificationsRead" class="btn btn-secondary">
                Mark All Read
            </a>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <p>No notifications</p>
        </div>
    <?php endif; ?>
</div>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
```

---

## 🔍 DEBUGGING TIPS

### Check What Data is Available:
Add this temporarily to any view:
```php
<pre><?php print_r($data); ?></pre>
```

### Common Issues:
1. **Undefined index error** → Use `$data['item'] ?? null` or check with `isset()`
2. **Empty array not handled** → Always use `!empty()` before foreach
3. **Date format error** → Make sure date column exists and is not null
4. **Number format error** → Ensure value is numeric before `number_format()`

---

## ⚡ QUICK WINS

You can get 80% functionality by updating just these 5 files:
1. `tenant/v_pay_rent.php`
2. `tenant/v_agreements.php`
3. `tenant/v_dashboard.php`
4. `landlord/v_dashboard.php`
5. `landlord/v_payment_history.php`

Each file takes ~15 minutes to update following the patterns above.

---

## 📚 REFERENCE

### Available Data by Controller:

**Tenant Controller:**
- `index()`: activeBooking, activeLease, pendingPayments, recentIssues, bookingStats
- `bookings()`: bookings, bookingStats
- `pay_rent()`: pendingPayments, paymentHistory, totalPayments, overduePayments
- `agreements()`: leases, activeLease, leaseStats
- `my_reviews()`: myReviews, reviewableBookings
- `notifications()`: notifications, unreadCount

**Landlord Controller:**
- `dashboard()`: propertyStats, bookingStats, pendingBookings, totalIncome, activeLeases, etc.
- `payment_history()`: payments, totalIncome, paymentStats
- `feedback()`: myReviews, reviewsAboutMe
- `notifications()`: notifications, unreadCount
- `income()`: totalIncome, paymentStats, payments, maintenanceStats, monthlyIncome

---

## ✅ VERIFICATION

After updating a view:
1. Login with test account
2. Navigate to the page
3. Check for PHP errors
4. Verify data displays correctly
5. Test empty states (if no data)
6. Test with multiple records

---

## 🎉 YOU'RE DONE!

Once you've updated the priority views, the system will be 100% functional for the core workflows:
- ✅ Tenant can book properties
- ✅ Landlord can approve bookings
- ✅ Lease agreements created
- ✅ Tenant can pay rent
- ✅ All data tracked in database

The remaining view updates are incremental improvements to display additional features like settings, detailed stats, etc.

---

**Need Help?**
- All backend logic is complete and tested
- Database has sample data for all features
- Follow the patterns shown above
- Start with the 5 priority files listed

**Estimated Time:**
- 5 priority views: ~1-2 hours
- All remaining views: ~4-6 hours

Good luck! 🚀
