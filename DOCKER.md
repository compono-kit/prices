## Docker

1. Create a `.env` file next to `docker-compose.yml` containing `GITHUB_TOKEN=<token>`
2. `docker-compose build`
3. `docker-compose run --rm prices_composer install`

The token is passed to Composer at runtime via `COMPOSER_AUTH` and is not stored in the image.

### Tests and static analysis

* `docker-compose run --rm prices_composer test`
* `docker-compose run --rm prices_composer analyse`
* `docker-compose run --rm prices_composer check`
