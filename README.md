# Raznar Gate Proxy Panel

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-4.x-4e56a6?style=for-the-badge&logo=livewire)
![FluxUI](https://img.shields.io/badge/Flux_UI-2.x-00D1FF?style=for-the-badge)
![Tailwind](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css)

Raznar Gate Proxy Panel is a high-performance management system designed for proxy hosting services. Built on the latest Laravel ecosystem, it provides a robust platform for managing nodes, servers, subscriptions, and financial transactions with a focus on security, scalability, and premium user experience.

## 🚀 Key Features

- **Advanced Node & Server Management**: Real-time monitoring and configuration of proxy nodes and client servers.
- **Flexible Subscription System**: Automated plan management, subscription lifecycle tracking, and renewal logic.
- **Immutable Credit Ledger**: Production-ready financial system with atomic balance updates, detailed transaction logs, and administrative reversal tools.
- **Secure API Infrastructure**: JWT-based authentication and API key management with granular usage tracking.
- **SSO Integration**: Seamless Single Sign-On capabilities for external integrations (e.g., Paymenter).
- **Premium Admin Interface**: Modern, reactive dashboard built with Livewire 4 and Flux UI components.
- **Docker Ready**: Fully containerized environment for consistent deployment across development and production.

## 🛠️ Technology Stack

- **Framework**: [Laravel 13](https://laravel.com)
- **Frontend**: [Livewire 4](https://livewire.laravel.com) & [Flux UI](https://fluxui.dev)
- **Styling**: [Tailwind CSS 4](https://tailwindcss.com)
- **Authentication**: [Laravel Fortify](https://laravel.com/docs/fortify) & JWT
- **Database**: MySQL / MariaDB
- **Infrastructure**: Docker & Docker Compose

## 📦 Installation

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & NPM
- Docker (Optional but recommended)

### Quick Start (Local)

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Raznar-Hosting/gate-proxy-panel.git
   cd gate-proxy-panel
   ```

2. **Run the setup script**:
   ```bash
   composer run setup
   ```
   *This will install dependencies, copy `.env.example`, generate keys, and run migrations.*

3. **Start the development server**:
   ```bash
   composer run dev
   ```

### Docker Deployment

To run the application using Docker:

```bash
docker-compose up -d
```

## 🧪 Testing

The project maintains high code quality through comprehensive testing.

```bash
# Run all tests
php artisan test

# Run tests with compact output
composer run test
```

## 🤝 Contributing

This project is primarily developed for **Raznar Hosting**. For internal contributions, please follow the established Git flow:
- Create a feature branch from `develop`.
- Ensure all tests pass and code is linted using `composer run lint`.
- Submit a PR to `develop`.

---

© 2026 [Raznar Hosting](https://raznar.id). All rights reserved.
