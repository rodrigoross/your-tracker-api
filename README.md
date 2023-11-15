# Your Tracker

Projeto feito com [Laravel](https://laravel.com/docs/10.x/routing), [Swoole](https://openswoole.com/)

## Local Deployment
1. Copiar arquivo `.env.example` como `.env`
2. Rodar `composer install --ignore-platform-reqs --no-scripts`
3. Trocar env `LARAVELS_INOTIFY_RELOAD` para `true` caso queira ativar o reload do _swoole automático
4. Rodar `php artisan key:generate`
5. Rodar `docker compose up -d`
6. Rodar `docker exec -it laravel php artisan migrate --seed` em ambiente de desenvolvimento
7. Rodar `npm install`
8. Se Linux/WSL, alterar permissões da pasta storage e bootstrap/cache ex: `sudo chmod 777 -R storage bootstrap/cache`
9. Acessar `http://localhost`
