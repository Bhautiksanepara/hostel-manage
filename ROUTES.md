# Route Inventory

Base local URL: `http://localhost/hostel-manage/`

This file maps the pages and route-style links in the Hostel Management project. Because this is a plain PHP app, routes are PHP file paths plus query strings, form actions, redirects, and AJAX/fetch endpoints.

## Summary

| Area | Route/page count | Main folder |
| --- | ---: | --- |
| Public/root tools | 7 | `/` |
| Student frontend pages | 16 | `frontend/user/pages/` |
| Admin frontend pages | 17 | `frontend/admin/pages/` |
| Gatekeeper frontend pages | 4 | `frontend/gatekeeper/` |
| User backend endpoints | 12 | `backend/user/` |
| Admin/backend endpoints and utilities | 12 | `backend/` |

## Public And Root Routes

| Page | URL | Purpose | Routes from this page |
| --- | --- | --- | --- |
| Main landing | `/index.php` | Public hostel landing page | `/index.php`, `/frontend/user/pages/login.php`, `/frontend/user/pages/register.php` |
| Project control panel | `/control_panel.php` | Project status and quick links | `/email_config.php`, `/db_test.php`, `/tests/login_test.php`, `/frontend/user/pages/login.php`, `/frontend/user/pages/register.php`, `/frontend/admin/pages/dashboard.php`, `/index.php` |
| Email config | `/email_config.php` | SMTP/email configuration | Self-post/config actions inside the page |
| DB test | `/db_test.php` | Database connection/table test page | Test utility page |
| Connection test | `/test_connection.php` | Database connection utility | Test utility page |
| QR visual test | `/qr_test_visual.php` | QR/payment visual test page | Test utility page |
| Login test | `/tests/login_test.php` | Authentication test utility | Test utility page |

## Student Frontend Routes

Student base path: `/frontend/user/pages/`

| Page | URL | Purpose | Routes from this page |
| --- | --- | --- | --- |
| Student landing | `/frontend/user/pages/index.php` | Student-facing landing page | `index.php`, `login.php`, `register.php` |
| Login | `/frontend/user/pages/login.php` | Student/admin login form | `POST login.php`; links to `register.php`, `forgetpass.php`; backend redirects admin to `../../admin/pages/dashboard.php`, student to `../../user/pages/dashboard.php` |
| Register | `/frontend/user/pages/register.php` | Student registration | `POST register.php`; link to `login.php`; email confirmation link to `/frontend/user/pages/confirm.php?email={email}&token={token}` |
| Confirm email | `/frontend/user/pages/confirm.php?email={email}&token={token}` | Confirms registration token | Redirects to `register.php` if token data is missing; link to `login.php` |
| Forgot password | `/frontend/user/pages/forgetpass.php` | Sends reset password email | `POST forgetpass.php`; link to `login.php`; email reset link to `/frontend/user/pages/newpassword.php?email={email}&token={token}` |
| New password | `/frontend/user/pages/newpassword.php?email={email}&token={token}` | Sets a new password | `POST ../../../backend/user/setPass.php`; link to `login.php` |
| Dashboard | `/frontend/user/pages/dashboard.php` | Student dashboard | Includes sidebar/topbar; topbar links to `dashboard.php` and `logout.php` |
| Hostel fees | `/frontend/user/pages/hostel-fees.php` | Student fee list, UPI payment, receipt upload | Direct UPI payment link; receipt upload `POST ../../../backend/user/upload_receipt.php`; sidebar routes |
| UPI payment | `/frontend/user/pages/upi_payment.php?fee_id={id}` | UPI payment view for one fee | UPI payment link; `dashboard.php`; `hostel-fees.php`; redirects unauthenticated users to `login.php` |
| Maintenance issue | `/frontend/user/pages/maintenance-issue.php` | Submit maintenance complaint | `POST ../../../backend/user/submit_issue.php`; sidebar routes |
| Maintenance history | `/frontend/user/pages/maintenance-history.php` | View submitted maintenance issues | Redirects unauthenticated users to `login.php`; sidebar routes |
| Gate pass request | `/frontend/user/pages/gate-pass.php` | Submit gate pass/leave request | `POST ../../../backend/user/gate-pass.php`; redirects unauthenticated users to `login.php`; sidebar routes |
| Gate pass status | `/frontend/user/pages/gate-pass-status.php` | View gate pass/leave status | Includes `../../../backend/user/gate-pass-status.php`; sidebar routes |
| Logout | `/frontend/user/pages/logout.php` | Ends student session | Redirects to `login.php` |
| Sidebar partial | `/frontend/user/pages/sidebar.php` | Student navigation partial | `hostel-fees.php`, `maintenance-issue.php`, `maintenance-history.php`, `gate-pass.php`, `gate-pass-status.php`, `forgetpass.php` |
| Topbar partial | `/frontend/user/pages/topbar.php` | Student topbar partial | `dashboard.php`, `logout.php` |

## Admin Frontend Routes

Admin base path: `/frontend/admin/pages/`

| Page | URL | Purpose | Routes from this page |
| --- | --- | --- | --- |
| Dashboard | `/frontend/admin/pages/dashboard.php` | Admin dashboard | Includes admin sidebar/topbar |
| Add fees | `/frontend/admin/pages/addfees.php` | Add/update hostel fees for students | `POST addfees.php`; sidebar routes |
| Hostel fees | `/frontend/admin/pages/hostelfees.php` | View student fee records and receipts | Includes `../../../backend/adminhostelfees.php`; receipt file links |
| Maintenance | `/frontend/admin/pages/maintainance.php` | Manage maintenance issues | `POST ../../../backend/admin_update_status.php`; issue image links |
| Gate pass and leave | `/frontend/admin/pages/gate-pass.php` | Approve/reject student gate pass requests | `POST gate-pass.php` through included backend; also contains unused fetch to `manage-requests.php` |
| Late student history | `/frontend/admin/pages/latestudent.php` | View/export late student history | AJAX `POST ../../../backend/adminlatestudent.php`; export form `POST ../../../backend/adminlatestudent.php` |
| Room allocation | `/frontend/admin/pages/room.php` | View room allocation | Includes `../../../backend/adminroom.php` |
| Room record | `/frontend/admin/pages/roomhistory.php` | Change rooms and view room history | AJAX `fetch_students.php`, `fetch_available_rooms.php`, `change_room.php` |
| Pending fees | `/frontend/admin/pages/pendingfees.php` | List students with pending fees | `POST ../../../backend/adminsend_reminder.php` |
| Payment verification | `/frontend/admin/pages/payment_verification.php` | Verify uploaded UPI receipts | Self-post actions inside page; receipt file links; redirects unauthenticated users to `../../user/pages/login.php` |
| UPI config | `/frontend/admin/pages/upi_config.php` | Configure UPI payment receiver details | Self-post actions inside page; redirects unauthenticated users to `../../user/pages/login.php` |
| Fetch students | `/frontend/admin/pages/fetch_students.php` | AJAX endpoint for room history | Returns student data |
| Fetch available rooms | `/frontend/admin/pages/fetch_available_rooms.php` | AJAX endpoint for room history | Returns available room data |
| Change room | `/frontend/admin/pages/change_room.php` | AJAX endpoint to update room assignment | Returns JSON; redirects missing session as JSON |
| Logout | `/frontend/admin/pages/logout.php` | Ends admin session | Redirects to `../../user/pages/login.php` |
| Admin sidebar partial | `/frontend/admin/pages/admin_sidebar.php` | Admin navigation partial | `addfees.php`, `hostelfees.php`, `maintainance.php`, `gate-pass.php`, `latestudent.php`, `room.php`, `roomhistory.php`, `pendingfees.php` |
| Admin topbar partial | `/frontend/admin/pages/admin_topbar.php` | Admin topbar partial | `dashboard.php`, `logout.php` |

## Gatekeeper Frontend Routes

Gatekeeper base path: `/frontend/gatekeeper/`

| Page | URL | Purpose | Routes from this page |
| --- | --- | --- | --- |
| Gatekeeper login | `/frontend/gatekeeper/login.php` | Gatekeeper login form | `POST login.php`; successful login redirects to `gatekeeper.php` |
| Gatekeeper panel | `/frontend/gatekeeper/gatekeeper.php` | OTR-based check out/check in/leave verification | `POST gatekeeper.php?mode={out|in|leave}`; redirects unauthenticated users to `login.php`; supports optional `debug=1` |
| Gatekeeper sidebar partial | `/frontend/gatekeeper/sidebar.php` | Gatekeeper navigation partial | `gatekeeper.php?mode=out`, `gatekeeper.php?mode=in`, `gatekeeper.php?mode=leave`, `logout.php` |
| Gatekeeper logout | `/frontend/gatekeeper/logout.php` | Ends gatekeeper session | Redirects to `login.php` |

## User Backend Endpoints

User backend base path: `/backend/user/`

| Endpoint | Called by | Purpose | Redirects/output |
| --- | --- | --- | --- |
| `/backend/user/login.php` | Included by student login page | Handles student/admin login | Admin: `../../admin/pages/dashboard.php`; student: `../../user/pages/dashboard.php` |
| `/backend/user/register.php` | Included by student register page | Creates student registration and sends confirmation mail | Confirmation URL points to `/frontend/user/pages/confirm.php` |
| `/backend/user/confirm.php` | Included by confirm page | Confirms email token | Redirects to `register.php` if query data missing |
| `/backend/user/forgetpass.php` | Included by forgot password page | Sends reset password email | Reset URL points to `/frontend/user/pages/newpassword.php` |
| `/backend/user/setPass.php` | New password form | Updates password from reset token | Redirects to `../../frontend/user/pages/login.php?resetStatus=success`; note: error redirects reference `resetPassword.php`, but the real page is `newpassword.php` |
| `/backend/user/newpassword.php` | Legacy/alternate password handler | Updates password | Redirects to `../../user/pages/login.php` after success |
| `/backend/user/dashboard.php` | Included by dashboard page | Loads student dashboard data | Requires student session |
| `/backend/user/hostel-fees.php` | Fee logic endpoint | Fee data logic | Backend support endpoint |
| `/backend/user/upload_receipt.php` | Hostel fees receipt upload form | Stores uploaded receipt | Redirects to `../../frontend/user/pages/hostel-fees.php` |
| `/backend/user/submit_issue.php` | Maintenance issue form | Creates maintenance issue | Redirects to `../../frontend/user/pages/maintenance-issue.php` |
| `/backend/user/gate-pass.php` | Gate pass request form | Creates gate pass/leave request | Redirects to `../../frontend/user/pages/gate-pass.php` |
| `/backend/user/gate-pass-status.php` | Included by status page | Loads gate pass status | Redirects unauthenticated users to `login.php` |

## Admin Backend Endpoints

Backend base path: `/backend/`

| Endpoint | Called by | Purpose | Redirects/output |
| --- | --- | --- | --- |
| `/backend/admingatepass.php` | Admin gate pass page | Loads and updates gate pass approvals | Redirects unauthenticated users to login |
| `/backend/adminhostelfees.php` | Admin hostel fees page | Loads fee records and receipt paths | Redirects unauthenticated users to login |
| `/backend/adminmaintainance.php` | Admin maintenance page | Loads maintenance issues | Redirects unauthenticated users to login |
| `/backend/admin_update_status.php` | Maintenance status form | Updates issue status | Redirects back to `HTTP_REFERER` |
| `/backend/adminlatestudent.php` | Late student page AJAX/export | Loads/exports late student report | JSON/PDF style output depending request |
| `/backend/adminroom.php` | Admin room page | Loads room data | Redirects unauthenticated users to login |
| `/backend/adminroomhistrory.php` | Room history support | Loads room history | Redirects unauthenticated users to login |
| `/backend/adminpendingfees.php` | Pending fees page | Loads pending fee students | Redirects unauthenticated users to login |
| `/backend/adminsend_reminder.php` | Pending fees reminder form | Sends reminder email | Redirects to `../frontend/admin/pages/pendingfees.php` |
| `/backend/UPIQRCodeGenerator.php` | User/admin payment pages | Generates UPI payment URLs/QR data | Included utility |
| `/backend/EmailHelper.php` | Registration/forgot/gate pass flows | Sends emails | Included utility |
| `/backend/dbconnection.php` | Many pages | Shared DB connection | Included utility |

## Important Navigation Flows

### Student Login Flow

1. User opens `/frontend/user/pages/login.php`.
2. Form posts back to `login.php`.
3. Backend checks credentials.
4. Admin user redirects to `/frontend/admin/pages/dashboard.php`.
5. Student user redirects to `/frontend/user/pages/dashboard.php`.

### Student Registration Flow

1. User opens `/frontend/user/pages/register.php`.
2. Form posts back to `register.php`.
3. Backend sends confirmation email.
4. Email link opens `/frontend/user/pages/confirm.php?email={email}&token={token}`.
5. Confirm page links user back to `login.php`.

### Forgot Password Flow

1. User opens `/frontend/user/pages/forgetpass.php`.
2. Form posts back to `forgetpass.php`.
3. Backend sends reset link.
4. Email link opens `/frontend/user/pages/newpassword.php?email={email}&token={token}`.
5. New password form posts to `/backend/user/setPass.php`.
6. Success redirects to `/frontend/user/pages/login.php?resetStatus=success`.

### Gate Pass Flow

1. Student opens `/frontend/user/pages/gate-pass.php`.
2. Form posts to `/backend/user/gate-pass.php`.
3. Admin reviews in `/frontend/admin/pages/gate-pass.php`.
4. Gatekeeper verifies OTR from `/frontend/gatekeeper/gatekeeper.php?mode=out`, `?mode=in`, or `?mode=leave`.
5. Student checks current status in `/frontend/user/pages/gate-pass-status.php`.

### Fees And Receipt Flow

1. Admin adds fees from `/frontend/admin/pages/addfees.php`.
2. Student views fees in `/frontend/user/pages/hostel-fees.php`.
3. Student pays through UPI link or `/frontend/user/pages/upi_payment.php?fee_id={id}`.
4. Student uploads receipt to `/backend/user/upload_receipt.php`.
5. Admin verifies uploaded receipt in `/frontend/admin/pages/payment_verification.php`.

## Known Route Issues To Check

| Reference | Found in | Issue |
| --- | --- | --- |
| `manage-requests.php` | `frontend/admin/pages/gate-pass.php` | This file does not exist in the repo. The page already uses a normal POST form, so this fetch block appears stale. |
| `resetPassword.php` | `backend/user/setPass.php` | The actual reset page appears to be `frontend/user/pages/newpassword.php`. Error redirects may go to a missing page. |
| `../../../frontend/user/pages/login.php` from backend admin files | Several `/backend/admin*.php` files | Path may resolve incorrectly from `/backend/` because it climbs too far. Confirm in browser if auth redirects fail. |
| `../../../global.css` in most admin pages | Admin pages | `frontend/global.css` exists, but a root-level `global.css` does not. From `frontend/admin/pages/`, `../../global.css` is the path that reaches `frontend/global.css`. |

## Quick Counts By Main UI Page

| Main UI page | Number of direct route links/actions found |
| --- | ---: |
| `/index.php` | 3 |
| `/control_panel.php` | 8 |
| `/frontend/user/pages/index.php` | 3 |
| `/frontend/user/pages/login.php` | 3 |
| `/frontend/user/pages/register.php` | 2 |
| `/frontend/user/pages/dashboard.php` | 2 through topbar plus 6 through sidebar |
| `/frontend/user/pages/hostel-fees.php` | 3 plus sidebar |
| `/frontend/user/pages/gate-pass.php` | 1 form action plus sidebar |
| `/frontend/admin/pages/dashboard.php` | 2 through topbar plus 8 through sidebar |
| `/frontend/admin/pages/gate-pass.php` | 1 active form action plus 1 stale fetch target |
| `/frontend/admin/pages/roomhistory.php` | 3 AJAX endpoints plus sidebar |
| `/frontend/gatekeeper/login.php` | 1 redirect after login |
| `/frontend/gatekeeper/gatekeeper.php` | 3 mode routes plus logout through sidebar |
