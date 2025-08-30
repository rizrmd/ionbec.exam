<h1 align="center">IONBEC</h1>

To install composer package using `docker` services.

1. Run `./nge compose up -d` (to run services)
2. Run `./nge composer install`

To install npm package using `asdf` (.tool-versions).

1. Run `asdf install`
2. Then run `npm install`

To run development server

1. Run `./nge compose up -d` (to run services)
2. Run `npm run watch` (to run development server)
3. Open http://localhost:8000

For first-setup

1. Run `./nge artisan migrate:fresh --seed`
2. Login using `admin` and `password`

