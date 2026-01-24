# SanctumEMHR EMHR - Mental Health EMR System

**Specialized Electronic Medical Health Record for Mental Health Clinicians**

[![Version](https://img.shields.io/badge/version-ALPHA-orange.svg)](https://github.com/yourusername/sacwan-openemr-mh)
[![License](https://img.shields.io/badge/license-Proprietary-red.svg)](LICENSE)
[![React](https://img.shields.io/badge/React-18+-blue.svg)](https://reactjs.org/)
[![PHP](https://img.shields.io/badge/PHP-8+-purple.svg)](https://php.net/)

---

## 🎯 Overview

SanctumEMHR EMHR is a modern, beautiful, and intuitive electronic medical health record system specifically designed for therapists and mental health clinicians. Built with a React frontend and PHP backend (currently using OpenEMR infrastructure), it provides advanced scheduling, patient management, and clinical documentation.

**Key Features:**
- ✨ Beautiful glassmorphic UI design
- 📅 Advanced recurring appointments with conflict detection
- 👥 Complete patient demographics and management
- 🔒 Secure session-based authentication
- 📊 Dashboard with real-time statistics
- 🔄 Series management (edit/delete single, all, or future occurrences)
- 🏥 Multi-provider and facility support

---

## 📚 Documentation

**Complete documentation is available in the [`docs/`](./docs/) folder.**

### Quick Links

**Getting Started:**
- 📖 [Complete Documentation Index](./docs/README.md)
- 🚀 [Installation Guide](./docs/setup/INSTALLATION.md)
- ⚙️ [Configuration](./docs/setup/CONFIGURATION.md) (coming soon)

**Architecture:**
- 🏗️ [System Architecture Overview](./docs/architecture/OVERVIEW.md)
- 🔧 [Technology Stack](./docs/architecture/TECH_STACK.md) (coming soon)
- 🔐 [Security Model](./docs/architecture/SECURITY.md) (coming soon)

**Development:**
- 💻 [API Endpoints Reference](./docs/api/ENDPOINTS.md)
- 🎨 [Component Library](./docs/components/README.md)
- 🗃️ [Database Tables](./docs/database/TABLES.md)
- 📝 [Development Guide](./docs/guides/DEVELOPMENT.md) (coming soon)

**Reference:**
- 📋 [TODO List](./docs/TODO.md)
- 📰 [Changelog](./docs/CHANGELOG.md)
- 🐛 [Known Issues](./docs/KNOWN_ISSUES.md)

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.0+
- MySQL 8.0+ or MariaDB 10.4+
- Node.js 18+
- Apache or Nginx
- OpenEMR installation

### Installation

```bash
# 1. Clone repository
git clone https://github.com/yourusername/sacwan-openemr-mh.git
cd sacwan-openemr-mh

# 2. Install frontend dependencies
cd react-frontend
npm install

# 3. Configure environment
cp .env.example .env
# Edit .env with your settings

# 4. Run development server
npm run dev

# Frontend: http://localhost:5173
# Backend API: http://localhost/custom/api
```

**Full installation instructions:** [docs/setup/INSTALLATION.md](./docs/setup/INSTALLATION.md)

---

## 🏗️ Architecture

```
┌─────────────────┐
│  React Frontend │  (Port 5173)
│   (Vite + React)│
└────────┬────────┘
         │ REST API
         ↓
┌─────────────────┐
│   PHP Backend   │  (Port 80/443)
│ Custom API Layer│
└────────┬────────┘
         │ MySQL
         ↓
┌─────────────────┐
│ MySQL Database  │
│  (OpenEMR Schema)│
└─────────────────┘
```

**Details:** [docs/architecture/OVERVIEW.md](./docs/architecture/OVERVIEW.md)

---

## ✨ Features

### Completed ✅

**Phase 3 - Series Management:**
- Edit/delete single occurrence, all occurrences, or "this and future"
- Series splitting for future occurrences
- Custom confirmation messages

**Phase 2 - Conflict Detection:**
- Pre-flight conflict checking before creating series
- Detailed conflict information
- User decision: Create anyway or cancel

**Phase 1 - Recurring Appointments:**
- Weekly patterns with specific days (Mon/Wed/Fri, etc.)
- Intervals: Weekly, Every 2/3/4 weeks
- End conditions: After X occurrences OR on specific date
- Backend validation and conflict checks

**Core Features:**
- Patient demographics and management
- Insurance tracking
- Emergency contacts/related persons
- Provider accounts and permissions
- Room/location tracking
- Appointment categories with colors
- Dashboard with statistics
- Session-based authentication

### Planned 📋

- Custom backend migration (away from OpenEMR)
- PostgreSQL database
- GraphQL API
- Real-time updates
- Mobile app
- Appointment reminders
- Billing integration

**Full roadmap:** [docs/TODO.md](./docs/TODO.md)

---

## 🛠️ Technology Stack

**Frontend:**
- React 18+
- Vite
- TailwindCSS
- React Router

**Backend:**
- PHP 8+
- OpenEMR framework
- MySQL/MariaDB

**Future Stack (Planned):**
- Node.js/TypeScript or Go
- PostgreSQL
- Prisma ORM
- GraphQL

---

## 📊 Project Status

**Current Version:** ALPHA
**Status:** Active Development
**Last Updated:** January 3, 2026

### Milestones

- ✅ Phase 1: Recurring Appointments Core (Dec 2025)
- ✅ Phase 2: Conflict Detection (Dec 2025)
- ✅ Phase 3: Series Management (Jan 2026)
- ✅ Complete Documentation (Jan 2026)
- 🚧 Phase 4: Backend Migration (Planned)
- 📋 Phase 5: Production Deployment (Planned)

---

## 🤝 Contributing

This is currently a private/proprietary project. For questions or collaboration:

**Contact:** Kenneth J. Nelan / Sacred Wandering

---

## 📄 License

**Proprietary and Confidential**

Copyright © 2026 Sacred Wandering
All Rights Reserved

This software is proprietary. Unauthorized copying, modification, distribution, or use is strictly prohibited.

---

## 🔗 Links

- **Documentation:** [/docs](/docs)
- **API Reference:** [/docs/api/ENDPOINTS.md](/docs/api/ENDPOINTS.md)
- **Changelog:** [/docs/CHANGELOG.md](/docs/CHANGELOG.md)
- **Issue Tracker:** [/docs/KNOWN_ISSUES.md](/docs/KNOWN_ISSUES.md)

---

## 💡 Support

For support, questions, or issues:
1. Check [Documentation](/docs)
2. Review [Known Issues](/docs/KNOWN_ISSUES.md)
3. Contact: Kenneth J. Nelan / Sacred Wandering

---

**Built with ❤️ for mental health clinicians**

*SanctumEMHR EMHR - Making mental health record keeping intuitive and beautiful*
