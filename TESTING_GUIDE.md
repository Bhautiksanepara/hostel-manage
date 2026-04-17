# Hostel Management System - Complete Setup & Testing Guide

## ✅ What We've Done

1. ✓ Imported database (`hostel-manage`)
2. ✓ Verified database configuration in `dbconnection.php`
3. ✓ Fixed index.php file paths
4. ✓ Created database test script

---

## 🧪 TESTING STEPS (Follow in Order)

### **STEP 1: Verify Database Connection**

1. Open your browser and go to: `http://localhost/hostel-manage/test_connection.php`
2. You should see:
   - ✓ Connection Successful
   - ✓ All tables listed with record counts
   - ✓ Sample data from users table

**Expected Output:**

```
✓ Connection Successful!

Database Tables Status:
✓ Table users exists
  → Records: 6
✓ Table rooms exists
  → Records: X
✓ Table gatepass exists
  → Records: 20
... etc
```

---

### **STEP 2: Test Admin Login**

1. Go to: `http://localhost/hostel-manage/`
2. Click **"Login"** button
3. Enter:
   - **Email:** `admin@example.com`
   - **Password:** `admin123`
4. Click **"Log In"**

**Expected Result:**

- You should be redirected to the admin dashboard
- You should see admin statistics and menu options

---

### **STEP 3: Test User Registration**

1. Go to: `http://localhost/hostel-manage/`
2. Click **"Sign Up"** button
3. Fill in the registration form with:
   - First Name: `Test`
   - Last Name: `User`
   - Email: `testuser@example.com`
   - OTR Number: `240099`
   - Password: `TestPass123`
   - Confirm Password: `TestPass123`
4. Click **"Register"**

**Expected Result:**

- Account should be created
- You should see a message requiring email verification
- Check your test_connection.php to see if the user was created

---

### **STEP 4: Test User Login (After Registration)**

1. Go to: `http://localhost/hostel-manage/`
2. Click **"Login"**
3. Enter test user credentials created above
4. Click **"Log In"**

**Expected Result:**

- User dashboard should load
- You should see room allocation, fees, and gate pass information

---

### **STEP 5: Test Admin Dashboard Features**

After admin login, test these features:

- **Dashboard** - View statistics
- **Room Management** - View room allocations
- **Gate Pass Approvals** - View pending requests
- **Fee Management** - View student fees
- **Maintenance Issues** - View reported issues
- **View Latest Students** - See recent registrations

---

## ⚠️ Common Issues & Solutions

### **Issue: "Connection Failed" on test_connection.php**

- **Solution:** Check if MySQL is running in XAMPP
- Open XAMPP Control Panel and ensure MySQL is started (green button)

### **Issue: "Table not found" errors**

- **Solution:** Database was not imported correctly
- Re-import the SQL file in phpMyAdmin

### **Issue: "Invalid credentials" on login**

- **Solution:** Check if admin user exists in admin_users table
- The test_connection.php script will automatically create it if missing

### **Issue: Images not loading**

- **Solution:** This is cosmetic; photos folder exists but may have missing images
- Doesn't affect functionality

### **Issue: CSS/Styling looks broken**

- **Solution:** This is normal; some CSS paths may need adjustment
- Functionality will still work

---

## 🔧 WHAT TO REPORT BACK

After testing, please provide:

1. **Database Test Results** - Did all tables show up with correct data?
2. **Admin Login** - Were you able to log in as admin?
3. **Admin Dashboard** - Did it load without errors?
4. **Registration** - Were you able to register a new user?
5. **Any Error Messages** - Screenshot or exact error text

---

## 📋 Next Steps After Testing

Once basic testing is complete, we can:

1. Configure email notifications (currently using hardcoded Gmail credentials)
2. Fix any path issues that appear during testing
3. Test PDF generation features (receipts, reports)
4. Set up file upload functionality for receipts and issue photos
5. Test the gate pass request system
6. Deploy to production with proper security settings

---

**START WITH STEP 1.** Let me know what you find! ✅
