# CareerTrack

A comprehensive career tracking and development platform built with Laravel and Livewire.

## Features

- Company and user management
- Goal setting and tracking
- Performance reviews and assessments
- Survey management
- Notification system
- Multi-location support
- Role-based access control

## Quick Start

### Local Development

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Copy `.env.example` to `.env` and configure
4. Generate app key: `php artisan key:generate`
5. Run migrations: `php artisan migrate`
6. Start development server: `php artisan serve`

### Docker Development

```bash
docker-compose up -d
```

### Kubernetes Deployment

For production deployment on Google Cloud Platform:

1. Navigate to the `k8s/` directory
2. Update configuration files with your values
3. Run the deployment script: `./deploy.sh`

See [k8s/README.md](k8s/README.md) for detailed Kubernetes deployment instructions.

## Technology Stack

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Livewire 3, Tailwind CSS
- **Database**: MySQL 8.0, SQLite (development)
- **Cache**: Redis
- **Container**: Docker
- **Orchestration**: Kubernetes (GKE)

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
