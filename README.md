# Laravel Solana aggregator

This is a simple laravel project that allows to fetch stakes and historical rewards on a given solana vote account (only on devnet now).

## Getting started
Install dependencies, create a new DB, setup your env variables, migrate the DB and you're good to go
```
composer install
cp .env.example .env
php artisan migrate
```

## Fetching stakes on given vote account
This command fetches stakes made on vote account specified in the environment **SOLANA_VOTE_ACCOUNT** and store the stakes in the DB stakes table.
```
php artisan stakes:fetch
```

## Fetching historical rewards for stakes
This command fetches rewards per epoch for all stakes in DB and store them in the DB table rewards.
```
php artisan rewards:fetch
```

