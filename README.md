# Your Tracker

With my co-worker, [Luiz Costa](https://github.com/LuizFelipeOC), responsible for the mobile implementation, we planned to create a free service for package tracking.
It allows you to track packages and provides real-time updates on their status.

## Table of Contents
- [Local Deployment](#local-deployment)
- [APIs](#apis)
- [Tech](#tech)
- [Features](#features)
- [Contributing](#contributing)
- [License](#license)

## Local Deployment

1. Copy the file `.env.example` and rename it as `.env`.
2. Install the dependencies by running: `composer install --ignore-platform-reqs --no-scripts`
3. Generate the application key by running: `php artisan key:generate`
4. Start the Docker containers by running: `docker compose up -d`
5. Run the database migrations and seed the database with sample data by running: `docker exec -it laravel php artisan migrate --seed`
6. Install the frontend dependencies by running: `npm install`
7. Build the frontend assets by running: `npm run build`
8. On Linux/WSL, update the permissions of the `bootstrap/cache` directory by running: `sudo chmod 777 -R storage bootstrap/cache`
9. Open your browser and visit `http://localhost` to access the application.

## APIs

This app uses the Link&Track API for package tracking. The API is free and can be used in personal apps. To use the API, you need to get in touch with:
```
https://api.linketrack.com/api?utm_source=footer
```

## Tech

The project is built with [Laravel](https://laravel.com/docs/10.x/), a PHP framework for web development.

## Features
- Package tracking.
- Notifies users of new packages status.

## Contributing

We welcome contributions from the community! If you'd like to contribute to the project, please follow these guidelines:

# License

This project is licensed under the Creative Commons Attribution (CC BY) license.

You are free to:

- Share: Copy and redistribute the material in any medium or format.
- Adapt: Remix, transform, and build upon the material for any purpose, even commercially.

Under the following terms:

- Attribution: You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.

To view the full license details, please visit [LICENSE.txt](LICENSE.txt).
