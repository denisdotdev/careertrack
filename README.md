# CareerTrack 🚀

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](CONTRIBUTING.md)

A modern career development and goal tracking platform built with Laravel, designed to help individuals and organizations manage career progression, set goals, and track achievements.

## ✨ Features

- **Company Management**: Create and manage company profiles with detailed information
- **Goal Tracking**: Set, monitor, and achieve career goals with progress tracking
- **Team Collaboration**: Organize teams within companies for better collaboration
- **Announcements**: Share important updates and announcements within organizations
- **User Management**: Comprehensive user system with authentication and authorization
- **Modern UI**: Clean, responsive interface built with modern web technologies

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Database**: MySQL/PostgreSQL/SQLite
- **Testing**: Pest PHP
- **Code Quality**: Laravel Pint
- **Development**: Laravel Sail (Docker)

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- Database (MySQL, PostgreSQL, or SQLite)

## 🚀 Quick Start

### Option 1: Docker (Recommended)

#### 1. Clone the Repository

```bash
git clone https://github.com/denisdotdev/careertrack.git
cd careertrack
```

#### 2. Build and Run with Docker

```bash
# Production build
docker-compose up -d

# Or for development
docker-compose --profile dev up -d
```

#### 3. Access the Application

- **Production**: http://localhost:8000
- **Development**: http://localhost:8001

#### 4. Run Migrations (if needed)

```bash
docker-compose exec app php artisan migrate
```

### Option 2: Local Development

#### 1. Clone the Repository

```bash
git clone https://github.com/denisdotdev/careertrack.git
cd careertrack
```

#### 2. Install Dependencies

```bash
composer install
npm install
```

#### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Configure Database

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

#### 5. Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed
```

#### 6. Start Development Server

```bash
# Using Laravel's built-in server
php artisan serve

# Or using the development script (includes queue, logs, and Vite)
composer run dev
```

Visit `http://localhost:8000` to see the application.

## 🧪 Testing

```bash
# Run all tests
composer test

# Run tests with coverage
composer test -- --coverage

# Run tests in Docker
docker-compose exec app php artisan test
```

## 🐳 Docker

### Production Build

```bash
# Build and run production container
docker-compose up -d

# View logs
docker-compose logs -f app

# Stop containers
docker-compose down
```

### Development Build

```bash
# Build and run development container
docker-compose --profile dev up -d

# Access development environment
docker-compose exec app-dev bash

# Run artisan commands
docker-compose exec app-dev php artisan migrate
```

### Custom Docker Commands

```bash
# Build image only
docker build -t careertrack .

# Run container with custom port
docker run -p 8080:80 careertrack

# Run with environment variables
docker run -p 8080:80 -e APP_ENV=local careertrack
```

## 🔄 Versioning

This project uses automatic semantic versioning based on [Conventional Commits](https://www.conventionalcommits.org/). The versioning system automatically runs on the main branch and:

- **Bumps patch version** (0.1.0 → 0.1.1) for bug fixes (`fix:` commits)
- **Bumps minor version** (0.1.0 → 0.2.0) for new features (`feat:` commits)  
- **Bumps major version** (0.1.0 → 1.0.0) for breaking changes (`BREAKING CHANGE` or `!:` commits)

### Commit Message Format

Follow the conventional commit format for automatic versioning:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Examples:**
- `feat: add user authentication system` (bumps minor)
- `fix: resolve login validation issue` (bumps patch)
- `feat!: remove deprecated API` (bumps major - breaking change)

### Branch Naming

- `feature/*` - New features (use `feat:` commits)
- `hotfix/*` - Bug fixes (use `fix:` commits)
- `release/*` - Major releases (use `BREAKING CHANGE` commits)

### Automatic Actions

- Version numbers are automatically updated in both `composer.json` and `package.json`
- Changelog is automatically generated and updated
- GitHub releases are created when pushing to main branch
- Git tags are created for each version
- Version changes are automatically synced to the develop branch
- Versioning only occurs on the main branch for safety

## 📚 API Documentation

API documentation is available at `/api/documentation` when running in development mode.

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details on how to submit pull requests, report bugs, and suggest features.

### Development Workflow

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and add tests
4. Run the test suite: `composer test`
5. Commit your changes: `git commit -m 'Add amazing feature'`
6. Push to the branch: `git push origin feature/amazing-feature`
7. Open a Pull Request

## 📖 Documentation

- [API Documentation](docs/api.md)
- [Installation Guide](docs/installation.md)
- [Deployment Guide](docs/deployment.md)
- [Contributing Guidelines](CONTRIBUTING.md)

## 🛡️ Security

If you discover a security vulnerability, please email us at `denislessard@protonmail.com`. Please do not open a public issue. See our [Security Policy](SECURITY.md) for more details.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Code of Conduct

This project adheres to the Contributor Covenant Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to `denislessard@protonmail.com`. See our [Code of Conduct](CODE_OF_CONDUCT.md) for details.

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- Icons and design inspiration from the open source community
- All contributors who help improve this project

## 📞 Support

- 📧 Email: `denislessard@protonmail.com`
- 🐛 Bug Reports: [GitHub Issues](https://github.com/denisdotdev/careertrack/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/denisdotdev/careertrack/discussions)
- 📖 Documentation: [Project Wiki](https://github.com/denisdotdev/careertrack/wiki)

---

**Made with ❤️ by [Denis Lessard](https://github.com/denisdotdev)**
