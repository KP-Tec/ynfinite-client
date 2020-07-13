# Ynfinite PHP Client

This is the ynfinite package for your PHP Client. 
To use it in your project you need a [ynfinite account](https://www.ynfinite.de/) to run it.


# How to Setup

You only need a couple of files to run Ynfinite. You can use the demo folder of our repository to get started, we already included a docker setup to get started right away.

## Create files and folders

Create the following files

 - index.php
 - composer.json
 - .htaccess
 - .env

Create the following folders

 - cache
 - templates
	 - namespaceof your layout




### index.php

        <?php  
    require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload  
    use Ypsolution\YnfinitePhpClient\YnfiniteClient;  
      
    $app = YnfiniteClient::create('templates');  
    $app->run();

### composer.json

    {  
        "name": "ypsolution/wvm",  
      "description": "Wvm 2020 Website",  
      "type": "project",  
      "require": {  
            "ypsolution/ynfinite-php-client": "dev-master"  
      }  
    }

### .htaccess

   

    <IfModule mod_rewrite.c>  
    RewriteEngine On  
    RewriteBase /  
    RewriteCond %{REQUEST_FILENAME} !-f  
    RewriteRule . /index.php [L]  
    RewriteRule ^index\.php$ - [L]  
    </IfModule>



## Development

### Setup

Go into the development folder and run the docker-composer inside of

    /development/docker

This will start a development enviroment that uses the files of the package placed in /src.
Place the templates you want to work with in the /development/ folder according to your namespace.

*IMPORTANT* If you use dev=true in your .env file it will use the lokal backend


