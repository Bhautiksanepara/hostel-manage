# 💳 UPI QR Code Payment System - Setup & Usage Guide

## 🎯 Overview

This UPI QR Code Payment System allows students to:

- **Generate QR codes** with their exact pending fee amount
- **Scan with Google Pay**, PhonePe, or any UPI app
- **Amount is locked** - cannot be changed by student
- **Pay directly** to hostel's UPI account
- **Upload receipt** for manual verification

Admin can:

- **Configure** hostel's UPI ID
- **Verify payments** manually
- **Approve/Reject** payment receipts
- **Track payment status**

---

## ✅ Latest Update (FIXED)

**QR Code Generation Issue - RESOLVED**

Previous implementation used Google Charts API which had limitations. Now uses **QR Server API** for:
- ✓ More reliable QR code generation
- ✓ No URL length limitations
- ✓ Better error handling
- ✓ Timeout protection (10 seconds)
- ✓ PNG validation

**Test URL:** `http://localhost/hostel-manage/qr_test_visual.php`

---

## 📦 What Was Created

### **1. Database Table: upi_config**

Stores your hostel's UPI configuration

```sql
CREATE TABLE upi_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upi_id VARCHAR(255) UNIQUE,
    receiving_name VARCHAR(255),
    merchant_category VARCHAR(100),
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

**Current Default Configuration:**

```
UPI ID: pateldham@upi
Receiving Name: Pateldham Hostel
Category: Education
```

---

## 🛠️ Backend Components

### **1. UPIQRCodeGenerator.php**

**Location:** `/backend/UPIQRCodeGenerator.php`

PHP class that generates UPI payment URLs and QR codes

- Methods:
  - `generateUPIURL()` - Creates UPI payment link
  - `generateQRCodeBase64()` - Generates QR as base64 image
  - `generateQRCodeFile()` - Saves QR code as PNG file
  - `setUPIID()` - Configure UPI manually
  - `getConfig()` - Retrieve current config

**Example Usage:**

```php
require_once 'backend/UPIQRCodeGenerator.php';

$qr_gen = new UPIQRCodeGenerator($conn);
$qr_image = $qr_gen->generateQRCodeBase64(15000, '240001', 'Student Name');
$upi_url = $qr_gen->generateUPIURL(15000, '240001', 'Student Name');
```

---

## 📱 Frontend Pages

### **1. Student Payment Page**

**Location:** `/frontend/user/pages/upi_payment.php`

**Features:**

- ✅ Shows pending fee details
- ✅ Displays QR code with locked amount
- ✅ Direct UPI payment link (fallback)
- ✅ Payment instructions
- ✅ After-payment receipt upload guidance

**Access Path:**

```
Student Dashboard → Pay Fees → Generate QR Code
or directly: /frontend/user/pages/upi_payment.php
```

**Display Elements:**

- Student OTR Number
- Student Name
- Academic Year
- **Amount Due (Locked) - ₹XXXXX**
- QR Code (300x300px)
- Step-by-step payment instructions
- Security & verification notes

---

### **2. Admin UPI Configuration**

**Location:** `/frontend/admin/pages/upi_config.php`

**Features:**

- ✅ Update UPI ID
- ✅ Set receiving name
- ✅ Choose merchant category
- ✅ Enable/Disable UPI payments
- ✅ View current configuration

**Admin Access:**

```
Admin Dashboard → UPI Configuration
```

**Configuration Fields:**
| Field | Example | Purpose |
|-------|---------|---------|
| UPI ID | `hostel@upi` or `9876543210@okaxis` | Where money received |
| Receiving Name | `Pateldham Hostel` | Shown to students |
| Category | `Education`, `Accommodation` | UPI classification |
| Status | Active/Inactive | Enable/Disable |

---

### **3. Admin Payment Verification**

**Location:** `/frontend/admin/pages/payment_verification.php`

**Features:**

- ✅ View all payment receipts
- ✅ Display student & payment details
- ✅ Preview receipt images
- ✅ Verify payments ✓
- ✅ Reject with reason
- ✅ Track verification status
- ✅ Statistics dashboard

**Admin Access:**

```
Admin Dashboard → Payment Verification
```

**Verification Workflow:**

1. Student uploads receipt screenshot
2. Receipt appears in "Pending Verification"
3. Admin reviews receipt
4. Admin clicks "Verify Payment" or "Reject Payment"
5. If verified:
   - Receipt status: VERIFIED
   - Fee status: PAID
   - Student receives confirmation
6. If rejected:
   - Rejection reason sent to student
   - Student can re-upload

**Statistics Shown:**

- 📊 Pending Verification (count)
- ✓ Verified Payments (count)
- ✗ Rejected Payments (count)

---

## 🔄 Payment Flow

```
STUDENT FLOW:
└─ Login to Dashboard
   └─ Navigate to "Pay Fees"
      └─ View Pending Amount (e.g., ₹15,000)
         └─ Click "Generate QR Code"
            └─ QR code generated with:
               • UPI: hostel@upi
               • Name: Pateldham Hostel
               • Amount: ₹15,000 (LOCKED)
               • Reference: OTR Number
            └─ Student scans with Google Pay/PhonePe
               └─ Amount auto-filled (cannot change)
                  └─ Student enters UPI PIN
                     └─ Payment successful
                        └─ Screenshot receipt
                           └─ Upload receipt to dashboard

ADMIN FLOW:
└─ Login to Dashboard
   └─ Navigate to "Payment Verification"
      └─ View pending payment receipts
         └─ Review receipt image
            └─ Verify Details:
               • Amount matches
               • Transaction ID exists
               • Receipt is clear
            └─ Click "Verify Payment"
               └─ Fee marked as PAID
                  └─ Student notified
```

---

## 💳 UPI ID Formats

### **Bank UPI IDs**

```
HDFC Bank:      name@okhdfcbank  or  name@ibl
Axis Bank:      name@okaxis
ICICI Bank:     name@okicici
SBI Bank:       name@sbi
Indian Bank:    name@indus
```

### **Payment App UPI IDs**

```
Google Pay:     name@googlepay
PhonePe:        name@phonepe
Paytm:          name@paytm
Amazon Pay:     name@amazonpay
```

### **Generic**

```
name@upi        (Generic UPI)
```

---

## 🔐 Security Features

1. **Amount Locked in QR:**
   - Amount encoded in UPI URL
   - Cannot be modified by student
   - Visible but not editable in UPI app

2. **Student Reference:**
   - OTR number included in transaction
   - Easy tracking and reconciliation

3. **Manual Verification:**
   - Admin manually verifies receipt
   - Prevents fraudulent payments
   - Screenshots checked for legitimacy

4. **Database Tracking:**
   - Payment records stored in `receipts` table
   - Status: Pending → Verified → Paid
   - Audit trail maintained

---

## 📊 Database Changes

### **New Tables:**

1. **upi_config** - UPI payment configuration

### **Updated Tables:**

None - uses existing `receipts` table with:

- `verified_by_admin` field
- `admin_verified_at` timestamp
- `rejection_note` field

---

## 🚀 Setup Steps

### **Step 1: Verify Database Tables**

```
Tables: upi_config, receipts (both exist) ✓
```

### **Step 2: Configure UPI Settings**

1. Login as Admin
2. Go to: `/frontend/admin/pages/upi_config.php`
3. Enter your hostel's UPI ID:
   - Example: `hostel@upi` or `9876543210@okaxis`
4. Enter receiving name (e.g., "Pateldham Hostel")
5. Select category (e.g., "Education")
6. Check "Active" to enable
7. Click "Save Configuration"

### **Step 3: Test Student Payment**

1. Login as Student
2. Go to: `/frontend/user/pages/upi_payment.php`
3. Click on pending fee
4. QR code should display with locked amount
5. Scan QR with Google Pay/PhonePe (test)
6. Verify UPI link opens with amount

### **Step 4: Test Payment Verification**

1. Login as Admin
2. Student uploads payment receipt (screenshot)
3. Go to: `/frontend/admin/pages/payment_verification.php`
4. Review receipt details
5. Click "Verify Payment" to approve
6. Fee status should change to "Paid"

---

## 📝 Important Notes

### ✅ What Works:

- ✓ QR code generation with **QR Server API** (reliable, no length limits)
- ✓ UPI URL encoding with locked amount
- ✓ Base64 image embedding in webpage
- ✓ Receipt upload validation
- ✓ Manual admin verification
- ✓ Status tracking
- ✓ Error handling and PNG validation
- ✓ Timeout protection (10 seconds)

### ⚠️ Limitations:

- **Manual Verification:** No auto-verification (by design)
- **Webhook:** Payment webhooks not configured
- **Receipt Validation:** Admin must manually verify
- **Refunds:** Not automated (manual processing needed)

### 🔧 Future Enhancements:

- Online payment gateway integration (Razorpay, PayU)
- Automated verification with webhook

---

## 🔧 Troubleshooting

### ❌ QR Code Not Generating?

**Problem:** "Failed to generate QR code" message

**Solution:**
1. Check internet connection (QR Server API needs network)
2. Verify UPI ID is configured in admin panel
3. Check error logs: `C:\xampp\logs\php_error.log`
4. Test with: `http://localhost/hostel-manage/qr_test_visual.php`

**Common Issues:**

| Issue | Cause | Fix |
|-------|-------|-----|
| Network timeout | QR Server is slow | Increase timeout in code (line 107 in UPIQRCodeGenerator.php) |
| Invalid PNG | QR API returned error | Check internet, try again |
| Amount not showing | UPI URL is null | Verify UPI config is set |
| QR scans but opens wrong | UPI encoding error | Check characters in OTR number |

### ✅ Testing the Fix

**Visual Test Page:**
```
http://localhost/hostel-manage/qr_test_visual.php
```

**Expected Results:**
- ✓ See QR code displayed on page
- ✓ QR code is scannable (clear and visible)
- ✓ Amount is ₹25,000.00 (locked)
- ✓ Receiver: Pateldham Hostel
- ✓ Can click "Or Click Here to Pay" button

**Mobile Test:**
1. Open test page on browser
2. On Android/iPhone: Scan QR code with camera
3. Opens UPI app (Google Pay/PhonePe)
4. Verify amount is pre-filled
5. Verify amount CANNOT be changed

### 🔍 Debug Information

**File Used:** `backend/UPIQRCodeGenerator.php`

**API Endpoint:** `https://api.qrserver.com/v1/create-qr-code/`

**Key Parameters:**
- `size`: 300x300 or 350x350 pixels
- `data`: Complete UPI URL with encoded parameters
- `timeout`: 10 seconds
- `error_log`: PHP error log on failure
- SMS/Email notifications after payment
- QR code expiry (optional)
- Fingerprint validation for receipts

---

## 🛠️ Troubleshooting

### **QR Code Not Displaying:**

```
Issue: Google Charts API not accessible
Solution: Check internet connection on server
         Or: Install local QR code library (endroid/qr-code)
```

### **UPI Link Not Opening:**

```
Issue: UPI app not installed on phone
Solution: Download Google Pay, PhonePe, Paytm
         Or: Use direct UPI link copy-paste
```

### **Amount Shows Wrong in Payment:**

```
Issue: Old cached QR code
Solution: Refresh page (Ctrl+R / Cmd+R)
         Or: Regenerate QR code
```

### **Payment Not Verified:**

```
Issue: Admin rejecting valid receipt
Solution: Check receipt is clear
         Check amount matches
         Check transaction ID visible
```

---

## 📚 File Locations

| File                                             | Purpose            | Access  |
| ------------------------------------------------ | ------------------ | ------- |
| `/backend/UPIQRCodeGenerator.php`                | QR generator class | Backend |
| `/frontend/user/pages/upi_payment.php`           | Student QR page    | Student |
| `/frontend/admin/pages/upi_config.php`           | Admin config       | Admin   |
| `/frontend/admin/pages/payment_verification.php` | Admin verify       | Admin   |
| `/uploads/qr_codes/`                             | Saved QR images    | Storage |
| `/uploads/receipts/`                             | Payment receipts   | Storage |

---

## 🧪 Testing Checklist

- [ ] Admin can configure UPI ID
- [ ] UPI configuration saves to database
- [ ] Student can generate QR code
- [ ] QR code displays correctly
- [ ] QR code amount is correct
- [ ] QR code links to UPI app (test device)
- [ ] Admin can view pending payments
- [ ] Admin can verify payments
- [ ] Admin can reject payments
- [ ] Fee status updates to "paid" after verification
- [ ] Rejection reason shown to student

---

## 💡 Usage Examples

### **Admin: Configure UPI**

```
1. Access: admin/pages/upi_config.php
2. Enter UPI ID: hostel@upi
3. Name: Pateldham Hostel
4. Category: Education
5. Enable: Check ✓ Active
6. Save
```

### **Student: Pay Fees**

```
1. Login to dashboard
2. Click "Pay Fees"
3. View pending amount (₹15,000)
4. Click "Generate QR"
5. See QR code with locked amount
6. Open Google Pay/PhonePe
7. Tap "Scan QR Code"
8. Scan QR code
9. Review payment (amount locked)
10. Enter UPI PIN
11. Screenshot receipt
12. Upload receipt to dashboard
```

### **Admin: Verify Payment**

```
1. Access: admin/pages/payment_verification.php
2. See pending receipts
3. Click "View Receipt" to see image
4. Verify:
   - Amount matches fee
   - Transaction ID visible
   - Receipt is clear
5. Click "✓ Verify Payment"
6. Fee marked as PAID
```

---

**System Status:** ✅ Ready to Use

**Setup Time:** ~10 minutes

**Support:** Contact admin for issues

---

Generated: April 9, 2026  
UPI Payment System v1.0
