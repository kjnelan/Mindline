# Known Issues & Limitations

**Current bugs and limitations in SanctumEMHR EMHR**

---

## Critical Issues

### 1. Admin Access Control Not Working
**Status:** 🔴 **OPEN**
**Priority:** HIGH
**Reported:** 2026-01-03

**Issue:**
Regular clinicians can still see the Administration/Settings menu despite restricting access to calendar admins only (calendar == 1).

**Root Cause:**
Likely related to OpenEMR's complex ACL (Access Control List) system. Simple field checks (`calendar == 1`) are insufficient.

**Current Implementation:**
```php
'admin' => ($user['calendar'] ?? 0) == 1
```

**Attempted Fixes:**
- Changed from `authorized || calendar` to only `calendar == 1`
- Still showing for regular providers

**Required Investigation:**
- Research OpenEMR's ACL system
- May need to use `acl_check()` functions instead of field checks
- Consider role-based permissions vs simple boolean flags

**Files Affected:**
- `custom/api/session_user.php:75`
- `custom/api/login.php:103`
- `custom/api/session_login.php:113`

**Workaround:**
None currently. All providers can access settings.

**Reference:**
See TODO.md item #4

---

## Medium Priority Issues

### 2. Modal Positioning
**Status:** 🟡 **PENDING**
**Priority:** MEDIUM

**Issue:**
Add/edit appointment modals appear at the top of the page instead of being centered in viewport.

**Expected Behavior:**
- Modal should be centered in current viewport
- Should scroll with page if content is tall
- Should use fixed positioning

**Components Affected:**
- AppointmentModal.jsx
- BlockTimeModal.jsx

**Workaround:**
Scroll to top of page before opening modal.

---

### 3. Appointment Refresh on Save
**Status:** 🟡 **PENDING**
**Priority:** MEDIUM

**Issue:**
After saving an appointment, the page performs a full reload (`window.location.reload()`) instead of optimistic UI update.

**Impact:**
- Slower UX
- Loses scroll position
- Network overhead

**Current Code:**
```javascript
const handleAppointmentSave = () => {
  window.location.reload(); // Not ideal
};
```

**Ideal Solution:**
- Optimistically update appointments array
- Refetch only appointments data
- Maintain scroll position

**Files:**
- `Dashboard.jsx:117`
- `Calendar.jsx` (similar pattern)

---

### 4. No Error Recovery on Network Failures
**Status:** 🟡 **PENDING**
**Priority:** MEDIUM

**Issue:**
If API calls fail due to network issues, there's no retry mechanism or recovery.

**Current Behavior:**
- Shows error message
- User must manually retry

**Ideal Solution:**
- Auto-retry with exponential backoff
- Offline detection
- Queue mutations for when back online

---

## Low Priority Issues

### 5. Patient Search Performance
**Status:** 🟡 **PENDING**
**Priority:** LOW

**Issue:**
Patient search queries database on every keystroke without debouncing.

**Impact:**
- Excessive database queries
- Slower with large patient lists (>1000 patients)

**Solution:**
Implement debouncing (300ms delay)

---

### 6. No Loading States for Long Operations
**Status:** 🟡 **PENDING**
**Priority:** LOW

**Issue:**
Some operations (like creating 52-week recurring series) don't show progress indicator.

**Affected Operations:**
- Creating large recurring series
- Bulk deletions
- Report generation (future feature)

**Solution:**
- Add loading spinners
- Progress bars for multi-step operations
- Optimistic UI updates where possible

---

### 7. Browser Back Button Behavior
**Status:** 🟡 **PENDING**
**Priority:** LOW

**Issue:**
Using browser back button doesn't maintain navigation state correctly.

**Current Behavior:**
- Back button goes to login page
- Doesn't preserve active nav tab

**Ideal Behavior:**
- Back button navigates between nav tabs
- Maintains scroll position
- Preserves filter states

---

## Limitations (By Design)

### 8. Single Database Session Per User
**Status:** ℹ️ **BY DESIGN**

**Limitation:**
One user can only have one active session. Logging in on a second device logs out the first.

**Reason:**
PHP session-based authentication with OpenEMR framework.

**Future:**
Token-based authentication will allow multiple sessions.

---

### 9. No Real-Time Updates
**Status:** ℹ️ **BY DESIGN**

**Limitation:**
Calendar doesn't update in real-time when other users make changes.

**Current Behavior:**
Must manually refresh to see other users' changes.

**Future:**
WebSocket implementation planned for real-time collaboration.

---

### 10. No Offline Support
**Status:** ℹ️ **BY DESIGN**

**Limitation:**
App requires active internet connection. No offline mode.

**Future:**
Progressive Web App (PWA) with offline support planned.

---

### 11. Browser Compatibility
**Status:** ℹ️ **TESTED ON**

**Fully Supported:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Not Tested:**
- Internet Explorer (not supported)
- Mobile browsers (partially working)
- Older browser versions

---

## Feature Limitations

### 12. Recurring Patterns Limited
**Status:** ℹ️ **BY DESIGN**

**Current Support:**
- Weekly patterns with specific days
- Intervals: 1, 2, 3, 4 weeks
- End: After X occurrences OR on date

**Not Supported:**
- Monthly patterns (1st Monday, last Friday, etc.)
- Complex patterns (every other Tuesday and Thursday)
- Exceptions/exclusions in series

**Future:**
May add more complex recurrence rules.

---

### 13. Single Facility Only
**Status:** ℹ️ **CURRENT SCOPE**

**Limitation:**
While database supports multiple facilities, UI currently assumes single facility.

**Impact:**
Multi-facility practices need workarounds.

**Future:**
Full multi-facility support planned.

---

### 14. No Appointment Reminders
**Status:** ℹ️ **NOT IMPLEMENTED**

**Limitation:**
No automated email/SMS appointment reminders.

**Future:**
Planned feature with notification system.

---

## Security Considerations

### 15. Session Timeout
**Status:** ℹ️ **NEEDS CONFIGURATION**

**Issue:**
Session timeout not explicitly configured. Uses PHP defaults.

**Current:**
~24 minutes (1440 seconds, PHP default)

**Recommendation:**
Configure appropriate timeout based on use case.

**File:**
`php.ini` or `.htaccess`

---

### 16. HTTPS Not Enforced
**Status:** ⚠️ **PRODUCTION WARNING**

**Issue:**
Development setup uses HTTP. Production MUST use HTTPS.

**Security Risk:**
Session cookies transmitted in clear text over HTTP.

**Requirement:**
SSL certificate and HTTPS enforcement for production.

---

### 17. No Rate Limiting
**Status:** ℹ️ **NOT IMPLEMENTED**

**Issue:**
API endpoints have no rate limiting.

**Risk:**
Vulnerable to brute force attacks and DoS.

**Future:**
Implement rate limiting middleware.

---

## Reporting Issues

### How to Report

1. Check this document first to see if already known
2. Gather information:
   - Steps to reproduce
   - Expected vs actual behavior
   - Browser and version
   - Screenshots if applicable
   - Error messages from console

3. Report to: Kenneth J. Nelan / Sacred Wandering

### Issue Template

```
**Title:** Brief description

**Priority:** Critical/High/Medium/Low

**Description:**
Detailed explanation of the issue

**Steps to Reproduce:**
1. Step one
2. Step two
3. ...

**Expected Behavior:**
What should happen

**Actual Behavior:**
What actually happens

**Screenshots:**
If applicable

**Environment:**
- Browser:
- OS:
- Version:

**Additional Context:**
Any other relevant information
```

---

## Fixed Issues

### ✅ Room Display Showing IDs
**Fixed:** 2026-01-03
**Issue:** Rooms displayed as "room1" instead of "Room 101"
**Solution:** Added JOIN to list_options table to fetch friendly names

### ✅ Dashboard Appointments Not Clickable
**Fixed:** 2026-01-03
**Issue:** Couldn't click appointments on dashboard to edit
**Solution:** Added onClick handler and modal state management

### ✅ Recurring Series Not Editable
**Fixed:** 2026-01-03 (Phase 3)
**Issue:** Could create recurring appointments but not edit/delete series
**Solution:** Implemented series management with single/all/future scopes

### ✅ Conflict Detection Missing
**Fixed:** 2025-12-30 (Phase 2)
**Issue:** Recurring appointments created even with conflicts
**Solution:** Added pre-flight conflict checking with user decision

---

**Last Updated:** January 3, 2026
**Review Frequency:** Weekly
