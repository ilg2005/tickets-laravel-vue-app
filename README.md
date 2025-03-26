# Ticket Management Project

This project is a Ticket Management System focused on learning PHP, Laravel, and Vue.js. It serves as a practical exploration of these technologies, providing hands-on experience in building a robust web application.

## Features

- **Laravel 11**: The latest version of the Laravel framework.
- **Breeze**: Simple, minimalistic starter kit for Laravel.
- **Inertia.js**: Allows you to create modern single-page React/Vue apps using classic server-side routing and controllers.
- **Vue**: JavaScript framework for building user interfaces.
- **PrimeVue**: A rich set of open-source UI components for Vue applications.
- **Spatie Laravel Permissions**: Powerful package for handling roles and permissions.

## Installation

### Prerequisites

- Docker
- Docker Compose

### Steps

1. **Clone the repository**

   ```sh
   git clone https://github.com/evertonpavan/tickets-laravel-vue-app.git
   cd tickets-laravel-vue-app
2. **Environment Setup**
    Create a .env file in the root directory by copying the example file and adjust the necessary configurations:

    ```sh
    cp .env.example .env
3. **Docker Setup**
    Build and run the Docker containers:

    ```sh
    docker-compose -f deploy/docker-compose.yml up -d
4. **Database Migration and Seeding**
    Run the following commands to migrate the database and seed the initial data:

    ```sh
    docker exec -t tickets-app php artisan migrate
    docker exec -t tickets-app php artisan db:seed   


## Usage
After setting up the environment, the application will be accessible at http://localhost:8080.

## License
This project is open-source and available under the MIT License.
