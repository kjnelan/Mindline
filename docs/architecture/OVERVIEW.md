# System Architecture Overview

**Mindline EMHR - Mental Health EMR System**

---

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENT TIER                          │
│  ┌────────────────────────────────────────────────────────┐ │
│  │              React Frontend (Port 5173)                │ │
│  │  • React 18+ with Vite                                 │ │
│  │  • TailwindCSS styling                                 │ │
│  │  • React Router navigation                             │ │
│  │  • Component-based architecture                        │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP/REST
                              │ CORS enabled
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      APPLICATION TIER                        │
│  ┌────────────────────────────────────────────────────────┐ │
│  │         PHP Custom API Layer (Port 80/443)             │ │
│  │  • Custom REST endpoints in /custom/api/               │ │
│  │  • Session-based authentication                        │ │
│  │  • CORS headers for React app                          │ │
│  │  • JSON request/response                               │ │
│  └────────────────────────────────────────────────────────┘ │
│                              │                               │
│  ┌────────────────────────────────────────────────────────┐ │
│  │           OpenEMR Framework Layer                      │ │
│  │  • globals.php - Database connection                   │ │
│  │  • SessionUtil - Session management                    │ │
│  │  • sqlStatement/sqlQuery - DB utilities                │ │
│  │  • UserService - User management                       │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ MySQL Protocol
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                         DATA TIER                            │
│  ┌────────────────────────────────────────────────────────┐ │
│  │        MySQL/MariaDB Database                          │ │
│  │  Database: mindline_emhr                               │ │
│  │  • 26 active tables from OpenEMR schema                │ │
│  │  • Custom indexes and optimizations                    │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## Architecture Layers

### 1. Client Tier (React Frontend)

**Location:** `/react-frontend/`
**Port:** 5173 (development), served via reverse proxy (production)
**Technology:** React 18+, Vite, TailwindCSS

**Key Components:**
```
react-frontend/src/
├── components/
│   ├── calendar/           # Calendar components
│   │   ├── AppointmentModal.jsx
│   │   ├── BlockTimeModal.jsx
│   │   ├── MiniCalendar.jsx
│   │   └── RecurrenceControls.jsx
│   ├── client/             # Patient management
│   ├── dashboard/          # Dashboard widgets
│   ├── layout/             # Layout components
│   └── settings/           # Settings components
├── pages/
│   ├── Calendar.jsx        # Main calendar view
│   ├── Admin.jsx           # Admin panel
│   └── Settings.jsx        # Settings page
├── hooks/
│   └── useAuth.js          # Authentication hook
└── utils/
    └── api.js              # API client library
```

**Responsibilities:**
- User interface rendering
- State management (React hooks)
- API calls to backend
- Client-side validation
- Routing and navigation

**Design Pattern:** Component-based with hooks for state management

---

### 2. Application Tier (PHP API Layer)

**Location:** `/custom/api/`
**Port:** 80 (HTTP) / 443 (HTTPS)
**Technology:** PHP 8+, OpenEMR framework

**API Endpoints:**

```
custom/api/
├── Authentication
│   ├── login.php              # POST - User login
│   ├── session_login.php      # POST - Session authentication
│   ├── session_user.php       # GET - Current user info
│   └── logout.php             # POST - Logout
│
├── Appointments
│   ├── get_appointments.php          # GET - Fetch appointments
│   ├── create_appointment.php        # POST - Create appointment
│   ├── update_appointment.php        # PUT - Update appointment
│   ├── delete_appointment.php        # DELETE - Delete appointment
│   └── get_appointment_categories.php # GET - Category list
│
├── Patients
│   ├── client_list.php        # GET - Patient list
│   ├── client_detail.php      # GET - Patient details
│   ├── client_demographics.php # GET - Demographics
│   ├── update_demographics.php # PUT - Update demographics
│   ├── create_client.php      # POST - Create patient
│   ├── patient_search.php     # GET - Search patients
│   └── client_stats.php       # GET - Statistics
│
├── Configuration
│   ├── get_rooms.php          # GET - Room list
│   ├── get_providers.php      # GET - Provider list
│   ├── get_list_options.php   # GET - Dropdown options
│   ├── get_calendar_settings.php    # GET - Calendar config
│   └── update_calendar_settings.php # PUT - Update config
│
└── [Other endpoints...]
```

**Responsibilities:**
- Request validation
- Business logic
- Database operations
- Session management
- CORS handling
- Error handling and logging

**Design Pattern:** RESTful API with procedural PHP

---

### 3. Data Tier (MySQL Database)

**Database:** `mindline_emhr`
**Technology:** MySQL 8+ or MariaDB 10+
**Schema:** OpenEMR-based (planned migration to custom schema)

**Core Tables:**
- `openemr_postcalendar_events` - Appointments
- `openemr_postcalendar_categories` - Calendar categories
- `patient_data` - Patient records
- `users` - Staff/providers
- `list_options` - Lookup lists
- [23 more tables - see DATABASE_TABLES.md]

**Responsibilities:**
- Data persistence
- Transaction management
- Data integrity
- Query optimization

---

## Request Flow

### Typical Request: Fetch Today's Appointments

```
1. USER ACTION
   └─> User navigates to Dashboard

2. REACT COMPONENT (Dashboard.jsx)
   └─> useAuth hook loads
       └─> api.getAppointments(today, today)

3. API CLIENT (utils/api.js)
   └─> fetch('http://backend/custom/api/get_appointments.php')
       Headers: credentials: 'include' (sends session cookie)

4. PHP API (get_appointments.php)
   ├─> Validate session ($_SESSION['authUserID'])
   ├─> Extract query parameters (start_date, end_date)
   ├─> Build SQL query with JOINs
   │   └─> SELECT FROM openemr_postcalendar_events
   │       JOIN patient_data, users, list_options, etc.
   ├─> Execute query via sqlStatement()
   ├─> Format results as JSON
   └─> Return HTTP 200 with JSON array

5. MYSQL DATABASE
   ├─> Execute SELECT query
   ├─> Apply indexes for optimization
   ├─> Return result set
   └─> Transaction committed

6. API CLIENT receives response
   └─> Parse JSON
   └─> Return to React component

7. REACT COMPONENT updates state
   └─> Re-render with appointment data
   └─> Display in AppointmentsList component
```

---

## Authentication Flow

```
1. LOGIN REQUEST
   User submits credentials
   └─> POST /custom/api/login.php
       {username, password}

2. AUTHENTICATION
   ├─> Verify credentials against users table
   ├─> Check password hash
   ├─> Validate active status
   └─> Create PHP session
       ├─> $_SESSION['authUser'] = username
       ├─> $_SESSION['authUserID'] = user_id
       └─> $_SESSION['authProvider'] = authorized flag

3. RESPONSE
   └─> Return user object + Set session cookie
       {success: true, user: {...}}

4. SUBSEQUENT REQUESTS
   ├─> Browser sends session cookie
   ├─> PHP validates session
   │   └─> Check $_SESSION['authUserID']
   └─> Request proceeds if valid
```

---

## State Management

### Frontend State

**Authentication State (useAuth hook):**
```javascript
{
  user: {
    id: number,
    name: string,
    initials: string,
    role: 'admin' | 'user',
    permissions: string[]
  },
  appointments: Array,
  loading: boolean
}
```

**Calendar State (Calendar.jsx):**
```javascript
{
  currentDate: Date,
  view: 'day' | 'week' | 'month',
  selectedProvider: string | 'all',
  appointments: Array,
  providers: Array,
  calendarSettings: Object
}
```

**Modal State:**
```javascript
{
  showAppointmentModal: boolean,
  editingAppointment: Object | null,
  modalInitialDate: string,
  modalInitialTime: string
}
```

### Backend State (PHP Session)

```php
$_SESSION = [
    'authUser' => 'username',
    'authUserID' => 123,
    'authProvider' => 1,
    'calendar' => 1
];
```

---

## Data Flow Patterns

### Create Appointment with Recurrence

```
1. User fills AppointmentModal
   ├─> Date, time, patient, provider
   ├─> Enables recurrence
   ├─> Selects days: Mon, Wed, Fri
   ├─> Frequency: Weekly
   └─> End after: 10 occurrences

2. Frontend validation
   ├─> Check all required fields
   ├─> Validate recurrence pattern
   └─> Calculate occurrence dates

3. API call: POST /create_appointment.php
   {
     patientId, providerId, categoryId,
     eventDate, startTime, duration,
     recurrence: {
       enabled: true,
       days: [1,3,5],
       frequency: 1,
       endCondition: 'after',
       occurrences: 10
     }
   }

4. Backend processing
   ├─> Validate all fields
   ├─> Generate occurrence dates
   │   └─> Calculate next 10 Mon/Wed/Fri dates
   ├─> Check ALL occurrences for conflicts
   │   └─> Query existing appointments
   │   └─> If conflicts found → Return 409
   ├─> Generate unique recurrence ID
   ├─> INSERT all occurrences
   │   └─> Each with same pc_recurrspec ID
   └─> Return success + created appointments

5. Frontend response handling
   ├─> If 409 (conflicts) → Show ConflictDialog
   ├─> If 200 (success) → Refresh calendar
   └─> Close modal
```

---

## Security Model

### Authentication
- **Session-based** authentication using PHP sessions
- **Password hashing** via OpenEMR's password utilities
- **Session timeout** configured in PHP settings
- **HTTPS required** in production

### Authorization
- **Role-based** access via user permissions
  - `authorized` = Provider/clinician access
  - `calendar` = Calendar administration
- **Provider filtering** - Users only see their own data by default
- **Admin routes** - Protected by permission checks

### CORS Security
```php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

### Input Validation
- **Backend validation** on all inputs
- **SQL injection prevention** via parameterized queries
- **XSS prevention** via output encoding
- **CSRF protection** via session validation

---

## Performance Considerations

### Database Optimization
- **Indexes** on frequently queried fields:
  - `pc_eventDate`, `pc_aid`, `pc_pid`
- **JOINs** optimized for common queries
- **Query limiting** via date ranges

### Frontend Optimization
- **Component memoization** where appropriate
- **Lazy loading** of routes
- **Debounced search** in patient search
- **Optimistic UI updates** for better UX

### Caching Strategy
- **Session caching** of user data
- **Frontend state caching** during session
- **No aggressive caching** to ensure data freshness

---

## Error Handling

### Backend Error Responses
```php
// Success
http_response_code(200);
echo json_encode(['success' => true, 'data' => $data]);

// Validation Error
http_response_code(400);
echo json_encode(['error' => 'Invalid input']);

// Unauthorized
http_response_code(401);
echo json_encode(['error' => 'Not authenticated']);

// Conflict (recurring appointment conflicts)
http_response_code(409);
echo json_encode(['error' => 'Conflicts detected', 'conflicts' => $conflicts]);

// Server Error
http_response_code(500);
echo json_encode(['error' => 'Internal server error']);
```

### Frontend Error Handling
```javascript
try {
  const response = await api.createAppointment(data);
  // Handle success
} catch (error) {
  if (error.status === 409) {
    // Show conflict dialog
  } else if (error.status === 401) {
    // Redirect to login
  } else {
    // Show error message
  }
}
```

---

## Logging & Monitoring

### Backend Logging
```php
error_log("Create appointment: User {$userId} creating appointment for patient {$patientId}");
error_log("SQL: " . $sql);
error_log("Error: " . $e->getMessage());
```

### Log Locations
- **PHP error log:** `/var/log/apache2/error.log` or configured path
- **OpenEMR log:** Database `log` table
- **Frontend console:** Browser developer tools

---

## Deployment Architecture

### Development
```
http://localhost:5173 (React dev server)
    └─> Proxy API calls to ─> http://backend/custom/api/
```

### Production
```
https://emr.example.com
    ├─> React build served via nginx/apache
    └─> API at /custom/api/ on same domain
```

---

## Future Architecture (Planned Migration)

### Target Architecture
```
┌──────────────┐
│   React App  │ (Same)
└──────┬───────┘
       │
       ↓
┌──────────────┐
│  Node.js/Go  │ (New custom API)
│  + GraphQL   │
└──────┬───────┘
       │
       ↓
┌──────────────┐
│ PostgreSQL   │ (New custom schema)
└──────────────┘
```

See [Database Migration Plan](../database/MIGRATION.md) for details.

---

**Last Updated:** January 3, 2026
**Version:** 1.0
