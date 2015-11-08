[![Build Status](https://travis-ci.org/jongotlin/Kompisbyran.svg)](https://travis-ci.org/jongotlin/Kompisbyran)

Kompisbyrån
========================

> Kompisbyrån finns för att fler ska bli integrerade i samhället med hjälp av språket. Målet med Kompisbyrån är att fler människor ska bli bättre på svenska och på så vis lättare komma in i samhället. Därför länkar vi samman personer som vill öva sin svenska med personer som vill hjälpa någon att förbättra sin svenska. En kompisfika är ett kulturellt utbyte mellan personer med olika bakgrund, erfarenheter, intressen och åldrar.

Kompisbyrån finns på [www.kompisbyran.se](http://www.kompisbyran.se). Den här sajten är inte lanserad än.


Setup (PHP >= 5.6)
------------

### Setup 

Install required components

```bash
$ php composer.phar install
```

### Config

Set default or specify values for connection string (later found in app/config/parameters.yml)

```php
    database_driver: pdo_mysql
    database_host: 127.0.0.1
    database_port: 8889
    database_name: kompisbyran
    database_user: root
    database_password: root
```

Create empty database with `php app/console doctrine:database:create`  
Create database schema with `php app/console doctrine:schema:create`  
Fill with fixture data with `php app/console doctrine:fixtures:load -n`

### Usage

```bash
$ php app/console server:run
```

Site is running at 127.0.0.1:8000


### Errors 

Make sure date.timezone is set in php.ini (ex. OSX etc/php.ini copy php.ini.default in it doesn't exist)

`date.timezone = "Europe/Amsterdam"`

