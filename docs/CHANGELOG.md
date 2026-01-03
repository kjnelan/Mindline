# Changelog

All notable changes to Mindline EMHR will be documented in this file.

---

## [Unreleased]

### Planned
- Custom backend migration (away from OpenEMR)
- PostgreSQL database
- GraphQL API
- Real-time updates via WebSockets
- Mobile app (React Native)

---

## [ALPHA] - 2026-01-03

### Added - Phase 3: Series Management
- ✅ Series management UI in AppointmentModal (blue banner)
- ✅ Series management UI in BlockTimeModal (purple banner)
- ✅ Edit/delete options: "Just this", "This and future", "All occurrences"
- ✅ Backend series update logic with dynamic WHERE clauses
- ✅ Backend series delete logic
- ✅ "This and future" split functionality (creates new recurrence ID)
- ✅ Custom delete confirmation messages based on scope

### Added - UI Improvements
- ✅ Dashboard appointments now clickable to edit
- ✅ Room numbers displayed in all views (Dashboard, Calendar Day/Week/Month)
- ✅ Room friendly names instead of IDs (e.g., "Room 101" vs "room1")

### Added - Documentation
- ✅ Complete database tables documentation (DATABASE_TABLES.md)
- ✅ All 26 tables cataloged with usage, priority, and migration plan
- ✅ Comprehensive docs/ folder structure
- ✅ Architecture overview
- ✅ Complete API endpoint reference
- ✅ Component library documentation
- ✅ Installation guide
- ✅ This changelog

### Fixed
- ✅ Admin access control (attempted fix, needs OpenEMR ACL investigation)
- ✅ Room display showing IDs instead of friendly names

---

## [ALPHA] - 2025-12-30

### Added - Phase 2: Conflict Detection & Resolution
- ✅ Conflict detection BEFORE creating recurring appointments
- ✅ Check ALL occurrences for scheduling conflicts
- ✅ Return detailed conflict information (date, time, reason)
- ✅ ConflictDialog component with scrollable conflict list
- ✅ "Create Anyway (Skip Conflicts)" button
- ✅ "Cancel" button to abort creation
- ✅ Visual conflict indicators

### Added - Phase 1: Recurring Appointments Core
- ✅ Recurrence UI in AppointmentModal and BlockTimeModal
- ✅ Day selection checkboxes (Sun-Sat)
- ✅ Frequency dropdown (Weekly, Every 2/3/4 weeks)
- ✅ End conditions: After X occurrences OR On specific date
- ✅ Form validation for recurrence fields
- ✅ Backend: Generate occurrence dates from recurrence rules
- ✅ Backend: Create all occurrences with shared recurrence ID
- ✅ Backend: Set pc_recurrtype=1 for recurring appointments
- ✅ Support for conflict override via overrideAvailability flag
- ✅ Return all created appointments with occurrence count

### Added - Calendar Features
- ✅ Calendar availability blocks (provider scheduling)
- ✅ Provider filtering (view specific provider's schedule)
- ✅ Room/location tracking
- ✅ Absolute positioning for multi-hour appointments
- ✅ Day/Week/Month views
- ✅ Mini calendar for date navigation
- ✅ Appointment categories with colors

### Added - Patient Management
- ✅ Patient list with search
- ✅ Patient demographics
- ✅ Patient detail pages
- ✅ New patient creation
- ✅ Emergency contacts/related persons
- ✅ Insurance tracking

### Added - Authentication & Users
- ✅ Session-based authentication
- ✅ Provider accounts
- ✅ Admin vs regular user permissions
- ✅ Multi-provider support

### Added - UI/UX
- ✅ Beautiful glassmorphic design with TailwindCSS
- ✅ Responsive layouts (mobile, tablet, desktop)
- ✅ Dashboard with today's appointments
- ✅ Quick actions
- ✅ Statistics widgets
- ✅ Modal-based workflows

---

## Version History

| Version | Date | Status | Key Features |
|---------|------|--------|--------------|
| ALPHA | 2026-01-03 | Current | Phase 3 complete, docs complete |
| ALPHA | 2025-12-30 | Previous | Phase 1-2 complete |
| ALPHA | 2025-12-15 | Previous | Initial calendar implementation |
| ALPHA | 2025-12-01 | Previous | Project inception |

---

## Migration Notes

### Breaking Changes
None yet - still in ALPHA

### Database Changes
- Added recurring appointment fields to postcalendar_events
- No schema migrations required (uses existing OpenEMR tables)

### API Changes
- Added recurring appointment endpoints
- Added conflict detection endpoints
- All backward compatible

---

## Known Issues

See [KNOWN_ISSUES.md](./KNOWN_ISSUES.md) for current bugs and limitations.

---

**Last Updated:** January 3, 2026
