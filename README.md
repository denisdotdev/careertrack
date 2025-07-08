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

### 1. Clone the Repository

```bash
git clone https://github.com/denisdotdev/careertrack.git
cd careertrack
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=careertrack
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed
```

### 6. Start Development Server

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
```

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
