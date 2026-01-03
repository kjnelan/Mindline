# API Endpoints Reference

**Complete REST API Documentation**

---

## Base URL

```
Development: http://localhost/custom/api/
Production:  https://emr.yourdomain.com/custom/api/
```

---

## Authentication Required

All endpoints except `/login.php` require authentication via PHP session cookie.

**Session Cookie:** `PHPSESSID`
**Credentials:** `include` in fetch requests

---

## 1. AUTHENTICATION

### 1.1 Login

**Endpoint:** `POST /login.php`

**Purpose:** Authenticate user and create session

**Request:**
```json
{
  "username": "john.doe",
  "password": "password123"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "user": {
    "id": 123,
    "username": "john.doe",
    "fname": "John",
    "lname": "Doe",
    "fullName": "John Doe",
    "authorized": 1,
    "admin": true
  }
}
```

**Errors:**
- `401` - Invalid credentials
- `400` - Missing username/password

---

### 1.2 Session Login

**Endpoint:** `POST /session_login.php`

**Purpose:** Validate existing session

**Request:** (No body, uses session cookie)

**Response:** `200 OK`
```json
{
  "success": true,
  "user": { ... }
}
```

**Errors:**
- `401` - Session invalid or expired

---

### 1.3 Get Current User

**Endpoint:** `GET /session_user.php`

**Purpose:** Get current authenticated user info

**Response:** `200 OK`
```json
{
  "id": 123,
  "username": "john.doe",
  "fname": "John",
  "lname": "Doe",
  "fullName": "John Doe",
  "authorized": 1,
  "calendar": 1,
  "admin": true,
  "facility": "Main Clinic",
  "facility_id": 1
}
```

**Errors:**
- `401` - Not authenticated

---

## 2. APPOINTMENTS & CALENDAR

### 2.1 Get Appointments

**Endpoint:** `GET /get_appointments.php`

**Purpose:** Fetch appointments for date range

**Query Parameters:**
- `start_date` (required) - YYYY-MM-DD
- `end_date` (required) - YYYY-MM-DD
- `provider_id` (optional) - Filter by provider

**Example:**
```
GET /get_appointments.php?start_date=2026-01-01&end_date=2026-01-31&provider_id=123
```

**Response:** `200 OK`
```json
{
  "success": true,
  "appointments": [
    {
      "id": 456,
      "eventDate": "2026-01-15",
      "startTime": "09:00:00",
      "endTime": "10:00:00",
      "duration": 60,
      "categoryId": 1,
      "categoryName": "Initial Consult",
      "categoryColor": "#3B82F6",
      "categoryType": 0,
      "apptstatus": "-",
      "status": "-",
      "title": "Initial Assessment",
      "comments": "New patient intake",
      "patientId": 789,
      "patientName": "Jane Smith",
      "patientDOB": "1990-05-15",
      "providerId": 123,
      "providerName": "John Doe",
      "facilityName": "Main Clinic",
      "room": "Room 101",
      "roomId": "room1",
      "isRecurring": false,
      "recurrenceId": null
    }
  ],
  "start_date": "2026-01-01",
  "end_date": "2026-01-31"
}
```

---

### 2.2 Create Appointment

**Endpoint:** `POST /create_appointment.php`

**Purpose:** Create new appointment or recurring series

**Request:**
```json
{
  "patientId": 789,
  "providerId": 123,
  "categoryId": 1,
  "eventDate": "2026-01-15",
  "startTime": "09:00",
  "duration": 60,
  "title": "Initial Assessment",
  "comments": "New patient intake",
  "status": "-",
  "room": "room1",

  "recurrence": {
    "enabled": true,
    "days": [1, 3, 5],
    "frequency": 1,
    "endCondition": "after",
    "occurrences": 10
  },

  "overrideAvailability": false
}
```

**Recurrence Object (optional):**
- `enabled` - boolean
- `days` - array of day numbers (0=Sun, 6=Sat)
- `frequency` - 1=weekly, 2=every 2 weeks, etc.
- `endCondition` - "after" | "on"
- `occurrences` - number (if endCondition="after")
- `endDate` - YYYY-MM-DD (if endCondition="on")

**Response:** `200 OK`
```json
{
  "success": true,
  "appointments": [...],
  "created_count": 10
}
```

**Conflict Response:** `409 Conflict`
```json
{
  "success": false,
  "error": "Conflicts detected",
  "conflicts": [
    {
      "date": "2026-01-15",
      "time": "09:00:00",
      "reason": "Provider unavailable - Out of Office"
    }
  ]
}
```

**Errors:**
- `400` - Invalid input
- `409` - Scheduling conflicts
- `401` - Not authenticated

---

### 2.3 Update Appointment

**Endpoint:** `PUT /update_appointment.php`

**Purpose:** Update existing appointment or series

**Request:**
```json
{
  "appointmentId": 456,
  "patientId": 789,
  "providerId": 123,
  "categoryId": 1,
  "eventDate": "2026-01-15",
  "startTime": "10:00",
  "duration": 60,
  "title": "Updated Title",
  "comments": "Updated notes",
  "status": "-",
  "room": "room2",

  "seriesUpdate": {
    "scope": "single",
    "recurrenceId": "recur_abc123"
  }
}
```

**Series Update Scopes:**
- `single` - Update only this occurrence
- `future` - Update this and all future occurrences (splits series)
- `all` - Update all occurrences in series

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Appointment updated",
  "updatedCount": 1
}
```

---

### 2.4 Delete Appointment

**Endpoint:** `DELETE /delete_appointment.php`

**Purpose:** Delete appointment or series

**Request:**
```json
{
  "appointmentId": 456,

  "seriesData": {
    "scope": "all",
    "recurrenceId": "recur_abc123"
  }
}
```

**Series Delete Scopes:**
- `single` - Delete only this occurrence
- `future` - Delete this and future occurrences
- `all` - Delete entire series

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Appointment deleted successfully",
  "deletedCount": 10
}
```

---

### 2.5 Get Appointment Categories

**Endpoint:** `GET /get_appointment_categories.php`

**Purpose:** Get all appointment categories

**Response:** `200 OK`
```json
{
  "success": true,
  "categories": [
    {
      "id": 1,
      "name": "Initial Consult",
      "color": "#3B82F6",
      "type": 0,
      "active": true
    },
    {
      "id": 2,
      "name": "Follow-up",
      "color": "#10B981",
      "type": 0,
      "active": true
    }
  ]
}
```

**Category Types:**
- `0` - Appointment (patient visit)
- `1` - Availability block (provider schedule)

---

## 3. PATIENTS

### 3.1 Get Patient List

**Endpoint:** `GET /client_list.php`

**Purpose:** Get list of all patients

**Query Parameters:**
- `active_only` (optional) - boolean

**Response:** `200 OK`
```json
{
  "success": true,
  "patients": [
    {
      "pid": 789,
      "fname": "Jane",
      "lname": "Smith",
      "DOB": "1990-05-15",
      "sex": "Female",
      "phone_home": "555-1234",
      "email": "jane@example.com",
      "status": "Active"
    }
  ]
}
```

---

### 3.2 Get Patient Detail

**Endpoint:** `GET /client_detail.php?pid=789`

**Purpose:** Get complete patient information

**Response:** `200 OK`
```json
{
  "success": true,
  "patient": {
    "pid": 789,
    "fname": "Jane",
    "lname": "Smith",
    "DOB": "1990-05-15",
    "sex": "Female",
    "phone_home": "555-1234",
    "phone_cell": "555-5678",
    "email": "jane@example.com",
    "street": "123 Main St",
    "city": "Anytown",
    "state": "CA",
    "postal_code": "12345",
    "status": "Active"
  }
}
```

---

### 3.3 Create Patient

**Endpoint:** `POST /create_client.php`

**Purpose:** Create new patient record

**Request:**
```json
{
  "fname": "Jane",
  "lname": "Smith",
  "DOB": "1990-05-15",
  "sex": "Female",
  "phone_home": "555-1234",
  "email": "jane@example.com",
  "street": "123 Main St",
  "city": "Anytown",
  "state": "CA",
  "postal_code": "12345"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "pid": 789,
  "message": "Patient created successfully"
}
```

---

### 3.4 Update Patient Demographics

**Endpoint:** `PUT /update_demographics.php`

**Purpose:** Update patient information

**Request:**
```json
{
  "pid": 789,
  "fname": "Jane",
  "lname": "Smith",
  "DOB": "1990-05-15",
  "phone_home": "555-1234",
  "email": "jane@example.com"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Demographics updated"
}
```

---

### 3.5 Search Patients

**Endpoint:** `GET /patient_search.php?query=smith`

**Purpose:** Search patients by name

**Query Parameters:**
- `query` (required) - Search term

**Response:** `200 OK`
```json
{
  "success": true,
  "patients": [
    {
      "pid": 789,
      "fname": "Jane",
      "lname": "Smith",
      "DOB": "1990-05-15",
      "label": "Smith, Jane (DOB: 05/15/1990)"
    }
  ]
}
```

---

### 3.6 Get Patient Stats

**Endpoint:** `GET /client_stats.php`

**Purpose:** Get patient statistics

**Response:** `200 OK`
```json
{
  "success": true,
  "activeClients": 150,
  "totalClients": 200
}
```

---

## 4. CONFIGURATION

### 4.1 Get Providers

**Endpoint:** `GET /get_providers.php`

**Purpose:** Get list of all providers

**Response:** `200 OK`
```json
{
  "success": true,
  "providers": [
    {
      "id": 123,
      "value": "123",
      "label": "Dr. John Doe",
      "authorized": 1
    }
  ]
}
```

---

### 4.2 Get Rooms

**Endpoint:** `GET /get_rooms.php`

**Purpose:** Get list of rooms/locations

**Response:** `200 OK`
```json
{
  "success": true,
  "rooms": [
    {
      "id": "room1",
      "name": "Room 101",
      "value": "room1",
      "label": "Room 101",
      "isDefault": false,
      "notes": ""
    }
  ]
}
```

---

### 4.3 Get List Options

**Endpoint:** `GET /get_list_options.php?list_id=sex`

**Purpose:** Get dropdown options for a specific list

**Query Parameters:**
- `list_id` (required) - List identifier

**Common List IDs:**
- `sex` - Gender options
- `marital` - Marital status
- `yesno` - Yes/No
- `rooms` - Rooms/locations

**Response:** `200 OK`
```json
{
  "success": true,
  "options": [
    {
      "option_id": "Male",
      "title": "Male",
      "seq": 1
    },
    {
      "option_id": "Female",
      "title": "Female",
      "seq": 2
    }
  ]
}
```

---

### 4.4 Get Calendar Settings

**Endpoint:** `GET /get_calendar_settings.php`

**Purpose:** Get calendar configuration

**Response:** `200 OK`
```json
{
  "success": true,
  "settings": {
    "interval": "15",
    "startHour": "8",
    "endHour": "18",
    "defaultDuration": "50"
  }
}
```

**Settings:**
- `interval` - Time slot interval in minutes (15, 30, 60)
- `startHour` - Calendar start hour (0-23)
- `endHour` - Calendar end hour (0-23)
- `defaultDuration` - Default appointment duration in minutes

---

### 4.5 Update Calendar Settings

**Endpoint:** `PUT /update_calendar_settings.php`

**Purpose:** Update calendar configuration

**Request:**
```json
{
  "interval": "15",
  "startHour": "8",
  "endHour": "18",
  "defaultDuration": "50"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Settings updated"
}
```

---

## 5. RELATED PERSONS

### 5.1 Get Related Persons

**Endpoint:** `GET /get_related_persons.php?pid=789`

**Purpose:** Get emergency contacts and related persons

**Response:** `200 OK`
```json
{
  "success": true,
  "related_persons": [
    {
      "id": 1,
      "person_id": 10,
      "fname": "John",
      "lname": "Smith",
      "phone_home": "555-9999",
      "relationship_type": "spouse"
    }
  ]
}
```

---

### 5.2 Save Related Person

**Endpoint:** `POST /save_related_person.php`

**Purpose:** Create or update related person

**Request:**
```json
{
  "pid": 789,
  "person_id": null,
  "fname": "John",
  "lname": "Smith",
  "phone_home": "555-9999",
  "relationship_type": "spouse"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "person_id": 10,
  "relation_id": 1
}
```

---

### 5.3 Delete Related Person

**Endpoint:** `DELETE /delete_related_person.php`

**Purpose:** Remove relationship

**Request:**
```json
{
  "relation_id": 1
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Relationship deleted"
}
```

---

## Error Responses

### Standard Error Format

```json
{
  "success": false,
  "error": "Error message"
}
```

### HTTP Status Codes

- `200 OK` - Success
- `400 Bad Request` - Invalid input
- `401 Unauthorized` - Not authenticated
- `403 Forbidden` - Not authorized
- `404 Not Found` - Resource not found
- `409 Conflict` - Scheduling conflict
- `500 Internal Server Error` - Server error

---

## CORS Headers

All endpoints include CORS headers:

```
Access-Control-Allow-Origin: http://localhost:5173
Access-Control-Allow-Credentials: true
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

---

## Rate Limiting

Currently: **None**
Planned: TBD

---

## API Versioning

Current: **v1** (implicit, no version in URL)
Future: **v2** may use `/api/v2/` prefix

---

**Last Updated:** January 3, 2026
**API Version:** 1.0
