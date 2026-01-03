# Component Library Documentation

**React Components Reference**

---

## Component Structure

```
react-frontend/src/components/
├── calendar/           # Calendar and scheduling components
├── client/            # Patient management components
├── dashboard/         # Dashboard widgets
├── layout/            # Layout and navigation
└── settings/          # Settings and admin components
```

---

## Calendar Components

### AppointmentModal

**Location:** `components/calendar/AppointmentModal.jsx`

**Purpose:** Create and edit appointments (including recurring series)

**Props:**
```typescript
{
  isOpen: boolean,
  onClose: () => void,
  onSave: () => void,
  appointment?: Object,  // For editing
  initialDate?: string,
  initialTime?: string,
  providers: Array
}
```

**Key Features:**
- Patient search and selection
- Provider selection
- Category selection
- Date/time picking
- Duration selection
- Room selection
- Recurring appointment creation
- Series management (edit single/all/future)
- Conflict detection

**State Management:**
```javascript
// Basic fields
const [patientId, setPatientId] = useState('');
const [providerId, setProviderId] = useState('');
const [categoryId, setCategoryId] = useState('');
const [eventDate, setEventDate] = useState('');
const [startTime, setStartTime] = useState('');
const [duration, setDuration] = useState(50);
const [room, setRoom] = useState('');

// Recurrence
const [recurrenceEnabled, setRecurrenceEnabled] = useState(false);
const [selectedDays, setSelectedDays] = useState([]);
const [frequency, setFrequency] = useState(1);
const [endCondition, setEndCondition] = useState('after');
const [occurrences, setOccurrences] = useState(10);

// Series editing
const [isEditingRecurringSeries, setIsEditingRecurringSeries] = useState(false);
const [seriesScope, setSeriesScope] = useState('single');
```

**Example Usage:**
```jsx
<AppointmentModal
  isOpen={showModal}
  onClose={() => setShowModal(false)}
  onSave={handleSave}
  appointment={selectedAppointment}
  providers={providerList}
/>
```

---

### BlockTimeModal

**Location:** `components/calendar/BlockTimeModal.jsx`

**Purpose:** Create and edit availability blocks

**Props:**
```typescript
{
  isOpen: boolean,
  onClose: () => void,
  onSave: () => void,
  block?: Object,
  initialDate?: string,
  initialTime?: string,
  providerId?: string
}
```

**Key Features:**
- Category selection (Out of Office, Vacation, etc.)
- Date/time selection
- Duration selection
- Recurring blocks
- Series management
- Comments/notes

**Similar to AppointmentModal** but simplified (no patient selection)

---

### MiniCalendar

**Location:** `components/calendar/MiniCalendar.jsx`

**Purpose:** Small month-view calendar for date selection

**Props:**
```typescript
{
  selectedDate: Date,
  onDateSelect: (date: Date) => void,
  highlightedDates?: Array<string>
}
```

**Features:**
- Month navigation
- Date selection
- Highlight specific dates
- Today indicator

---

## Dashboard Components

### AppointmentsList

**Location:** `components/dashboard/AppointmentsList.jsx`

**Purpose:** Display today's appointments on dashboard

**Props:**
```typescript
{
  todaysAppointments: Array<{
    time: string,
    client: string,
    type: string,
    duration: string,
    room: string,
    isNext: boolean
  }>,
  onAppointmentClick: (appointment) => void
}
```

**Features:**
- Clickable appointments
- Shows time, patient, type, duration, room
- Highlights next appointment
- Responsive design

**Example:**
```jsx
<AppointmentsList
  todaysAppointments={appointments}
  onAppointmentClick={handleClick}
/>
```

---

### StatsGrid

**Location:** `components/dashboard/StatsGrid.jsx`

**Purpose:** Display key statistics

**Props:**
```typescript
{
  stats: {
    todayAppointments: { value, trend },
    unbilledAppointments: { value, trend },
    sessionsYTD: { value, trend },
    activeClients: { value, trend }
  }
}
```

---

### QuickActions

**Location:** `components/dashboard/QuickActions.jsx`

**Purpose:** Quick action buttons

**Props:**
```typescript
{
  onNewClient: () => void,
  onNewAppointment: () => void
}
```

---

## Layout Components

### AppShell

**Location:** `components/layout/AppShell.jsx`

**Purpose:** Main application layout wrapper

**Props:**
```typescript
{
  user: Object,
  activeNav: string,
  setActiveNav: (nav: string) => void,
  today: string,
  onLogout: () => void,
  wide: boolean,
  children: ReactNode
}
```

**Features:**
- Navigation bar
- Header with user menu
- Content area
- Responsive layout

---

### NavBar

**Location:** `components/layout/NavBar.jsx`

**Purpose:** Main navigation tabs

**Props:**
```typescript
{
  activeNav: string,
  setActiveNav: (nav: string) => void,
  user: Object
}
```

**Tabs:**
- Dashboard
- Clients
- Calendar
- Messages (coming soon)
- Billing (coming soon)
- Reports
- Settings (admin only)

---

### UserMenu

**Location:** `components/layout/UserMenu.jsx`

**Purpose:** User dropdown menu

**Props:**
```typescript
{
  user: { name, initials },
  onLogout: () => void
}
```

---

## Client Components

### NewClientModal

**Location:** `components/client/NewClientModal.jsx`

**Purpose:** Create new patient

**Props:**
```typescript
{
  onClose: () => void,
  onClientCreated: (patientId: number) => void
}
```

**Fields:**
- First name, last name
- Date of birth
- Gender
- Contact information
- Address

---

### DemographicsTab

**Location:** `components/client/DemographicsTab.jsx`

**Purpose:** Display/edit patient demographics

**Props:**
```typescript
{
  patient: Object,
  onUpdate: () => void
}
```

**Sections:**
- Basic information
- Contact details
- Address
- Insurance
- Emergency contacts

---

## Settings Components

### CalendarSettings

**Location:** `components/admin/CalendarSettings.jsx`

**Purpose:** Configure calendar settings

**Settings:**
- Time interval (15/30/60 minutes)
- Start/end hours
- Default appointment duration

---

## Common Patterns

### Modal Pattern

All modals follow this pattern:

```jsx
function MyModal({ isOpen, onClose, onSave }) {
  const [data, setData] = useState({});
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  const handleSubmit = async () => {
    try {
      await api.saveData(data);
      setSuccess('Saved successfully');
      setTimeout(() => {
        onSave();
        onClose();
      }, 1500);
    } catch (err) {
      setError(err.message);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        {/* Form fields */}
        {error && <div className="error">{error}</div>}
        {success && <div className="success">{success}</div>}
        <button onClick={handleSubmit}>Save</button>
        <button onClick={onClose}>Cancel</button>
      </div>
    </div>
  );
}
```

---

### API Call Pattern

```jsx
const [loading, setLoading] = useState(false);
const [error, setError] = useState(null);

const fetchData = async () => {
  setLoading(true);
  setError(null);
  try {
    const response = await api.getData();
    setData(response.data);
  } catch (err) {
    setError(err.message);
  } finally {
    setLoading(false);
  }
};
```

---

### Form Validation Pattern

```jsx
const validateForm = () => {
  const errors = {};

  if (!patientId) errors.patient = 'Patient is required';
  if (!eventDate) errors.date = 'Date is required';
  if (!startTime) errors.time = 'Time is required';

  setFormErrors(errors);
  return Object.keys(errors).length === 0;
};

const handleSubmit = () => {
  if (!validateForm()) return;
  // Proceed with submission
};
```

---

## Styling

### Tailwind Classes

**Common Patterns:**

**Glassmorphic Cards:**
```jsx
className="backdrop-blur-2xl bg-white/40 rounded-3xl shadow-2xl border border-white/50 p-8"
```

**Buttons:**
```jsx
// Primary
className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"

// Secondary
className="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg"

// Danger
className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg"
```

**Form Inputs:**
```jsx
className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
```

---

## State Management

### Global State (useAuth Hook)

```jsx
import { useAuth } from './hooks/useAuth';

function MyComponent() {
  const { user, appointments, loading } = useAuth();

  if (loading) return <Loading />;

  return <div>{user.name}</div>;
}
```

**useAuth provides:**
- `user` - Current user object
- `appointments` - Today's appointments
- `loading` - Loading state

---

### Local State

Use useState for component-local state:

```jsx
const [showModal, setShowModal] = useState(false);
const [selectedItem, setSelectedItem] = useState(null);
const [formData, setFormData] = useState({});
```

---

## API Integration

### Using API Client

```jsx
import { getAppointments, createAppointment } from './utils/api';

// Fetch data
const appointments = await getAppointments(startDate, endDate);

// Create data
const result = await createAppointment(appointmentData);
```

See [API Documentation](../api/ENDPOINTS.md) for all endpoints.

---

## Best Practices

### 1. Component Organization

```jsx
// 1. Imports
import { useState, useEffect } from 'react';
import { api } from './utils/api';

// 2. Component
function MyComponent({ prop1, prop2 }) {
  // 3. State declarations
  const [data, setData] = useState([]);

  // 4. Effects
  useEffect(() => {
    fetchData();
  }, []);

  // 5. Handlers
  const handleClick = () => {
    // ...
  };

  // 6. Render
  return <div>...</div>;
}

// 7. Export
export default MyComponent;
```

### 2. Error Handling

Always handle errors gracefully:

```jsx
try {
  await api.saveData(data);
  setSuccess('Success!');
} catch (error) {
  setError(error.message || 'An error occurred');
  console.error('Error:', error);
}
```

### 3. Accessibility

- Use semantic HTML
- Include ARIA labels
- Keyboard navigation support
- Focus management in modals

### 4. Performance

- Use `useMemo` for expensive computations
- Implement virtual scrolling for long lists
- Lazy load routes with React.lazy()

---

**Last Updated:** January 3, 2026
**Version:** 1.0
