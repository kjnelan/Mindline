# SanctumEMHR EMHR - Complete Documentation

**Mental Health EMR System**
**Version:** ALPHA
**Last Updated:** 2026-01-03
**Author:** Kenneth J. Nelan / Sacred Wandering

---

## 📚 Documentation Index

### Getting Started
- **[Setup Guide](./setup/INSTALLATION.md)** - Installation and initial configuration
- **[Quick Start](./setup/QUICKSTART.md)** - Get up and running in 5 minutes
- **[Configuration](./setup/CONFIGURATION.md)** - Environment and settings

### Architecture & Design
- **[System Architecture](./architecture/OVERVIEW.md)** - High-level system design
- **[Technology Stack](./architecture/TECH_STACK.md)** - Technologies and frameworks
- **[Data Flow](./architecture/DATA_FLOW.md)** - How data moves through the system
- **[Security Model](./architecture/SECURITY.md)** - Authentication and authorization

### Database
- **[Database Tables](./database/TABLES.md)** - All tables and their usage
- **[Schema Design](./database/SCHEMA.md)** - Current and future schema
- **[Migration Plan](./database/MIGRATION.md)** - Moving away from OpenEMR
- **[Relationships](./database/RELATIONSHIPS.md)** - Table relationships and constraints

### API Documentation
- **[API Overview](./api/README.md)** - REST API design and conventions
- **[Authentication](./api/AUTHENTICATION.md)** - Login and session management
- **[Appointments](./api/APPOINTMENTS.md)** - Calendar and scheduling endpoints
- **[Patients](./api/PATIENTS.md)** - Patient management endpoints
- **[All Endpoints](./api/ENDPOINTS.md)** - Complete endpoint reference

### Frontend Components
- **[Component Library](./components/README.md)** - React component documentation
- **[Dashboard](./components/DASHBOARD.md)** - Dashboard components
- **[Calendar](./components/CALENDAR.md)** - Calendar system
- **[Modals](./components/MODALS.md)** - Modal components
- **[State Management](./components/STATE.md)** - React hooks and state

### Development Guides
- **[Development Workflow](./guides/DEVELOPMENT.md)** - How to develop features
- **[Git Workflow](./guides/GIT_WORKFLOW.md)** - Branching and commits
- **[Testing](./guides/TESTING.md)** - Testing strategy (planned)
- **[Code Standards](./guides/CODE_STANDARDS.md)** - Coding conventions

### Features
- **[Recurring Appointments](./guides/RECURRING_APPOINTMENTS.md)** - Phase 1-3 implementation
- **[Calendar Management](./guides/CALENDAR_FEATURES.md)** - Calendar features
- **[Patient Management](./guides/PATIENT_FEATURES.md)** - Patient features

### Reference
- **[TODO List](./TODO.md)** - Current tasks and future enhancements
- **[Changelog](./CHANGELOG.md)** - Version history
- **[Known Issues](./KNOWN_ISSUES.md)** - Current bugs and limitations

---

## 🎯 Quick Links

**For Developers:**
- [Setup Development Environment](./setup/INSTALLATION.md)
- [API Endpoint List](./api/ENDPOINTS.md)
- [Component Reference](./components/README.md)

**For Administrators:**
- [Installation Guide](./setup/INSTALLATION.md)
- [Configuration Guide](./setup/CONFIGURATION.md)
- [Security Setup](./architecture/SECURITY.md)

**For Planning:**
- [Database Migration Plan](./database/MIGRATION.md)
- [Technology Roadmap](./architecture/TECH_STACK.md)
- [TODO List](./TODO.md)

---

## 📖 About This Documentation

This documentation covers the SanctumEMHR EMHR system, a specialized mental health EMR built on OpenEMR infrastructure with a modern React frontend.

### Documentation Philosophy

- **Complete:** Every feature, endpoint, and component documented
- **Practical:** Real examples and code snippets
- **Current:** Updated with each significant change
- **Organized:** Easy to navigate and find information

### How to Use This Documentation

1. **New to the project?** Start with [Quick Start](./setup/QUICKSTART.md)
2. **Setting up?** Follow [Installation Guide](./setup/INSTALLATION.md)
3. **Developing?** Check [Development Workflow](./guides/DEVELOPMENT.md)
4. **Need an endpoint?** See [API Endpoints](./api/ENDPOINTS.md)
5. **Planning migration?** Review [Database Migration](./database/MIGRATION.md)

---

## 🏗️ System Overview

**SanctumEMHR EMHR** is a mental health-focused Electronic Medical Health Record system designed for therapists and mental health clinicians.

### Key Features

✅ **Advanced Calendar System**
- Day/Week/Month views
- Recurring appointments (weekly patterns, intervals)
- Availability block management
- Conflict detection
- Series management (edit/delete single, all, or future)

✅ **Patient Management**
- Complete demographics
- Insurance tracking
- Emergency contacts
- Related persons
- Document management

✅ **Modern React UI**
- Beautiful glassmorphic design
- Responsive layouts
- Real-time updates
- Intuitive modals

✅ **Multi-Provider Support**
- Provider filtering
- Room/location tracking
- Facility management

### Current Status

**Version:** ALPHA
**Phase:** Active Development
**Backend:** OpenEMR (planned migration to custom backend)
**Frontend:** React 18+ (complete custom build)

---

## 🔧 Technology Stack

### Frontend
- **React 18+** - UI framework
- **React Router** - Navigation
- **TailwindCSS** - Styling
- **Vite** - Build tool

### Backend
- **PHP 8+** - Server language
- **OpenEMR** - Current backend framework
- **MySQL/MariaDB** - Database

### Future Stack (Planned)
- **Node.js/TypeScript** or **Go** - API server
- **PostgreSQL** - Database
- **Prisma** - ORM (if TypeScript)
- **GraphQL** - API layer (optional)

---

## 📞 Support & Contributing

### Questions?
- Check existing documentation first
- Review [Known Issues](./KNOWN_ISSUES.md)
- Contact: Kenneth J. Nelan / Sacred Wandering

### Contributing
1. Read [Development Workflow](./guides/DEVELOPMENT.md)
2. Follow [Code Standards](./guides/CODE_STANDARDS.md)
3. Use [Git Workflow](./guides/GIT_WORKFLOW.md)

---

## 📄 License

**Proprietary and Confidential**
Copyright © 2026 Sacred Wandering
All Rights Reserved

---

**Last Updated:** January 3, 2026
**Documentation Version:** 1.0
**System Version:** ALPHA
