# Ynfinite PHP Client

This is the ynfinite package for your PHP Client.
To use it in your project you need a [ynfinite account](https://www.ynfinite.de/) to run it.

# How to Setup

## Development

Go into the development folder and run the docker-composer inside of

    cd /development/docker
    docker-compose build
    docker-compose up -d
    docker-compose exec ynfinite-client composer install

This will start a local DEV environment and you can open [http://localhost](http://localhost/)

### Troubleshooting

#### Cache dir not writable

```
Uncaught RuntimeException: Route collector cache file directory /var/www/config/../tmp/cache is not writable
```

Simply run for development purpose

```
docker-compose exec ynfinite-client chmod 777 tmp -R
```
