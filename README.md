## Install steps

- clone repo
- composer install
- copy .env.example and rename it to .env
- setup db and set it up in the env
- run : ```php artisan key:generate```
- run : ```php artisan migrate:fresh --seed```
- postman collection exists in the root ```Wallets.postman_collection.json```
