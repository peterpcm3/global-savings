#Order with vouchers Case Study

## About the study

Symfony based REST API for serving order and vouchers functionality. Order is defined with specific amount and  
can use a voucher as an option. The voucher can bring discount to the order amount if it is not expired and not used.
The relation between orders and vouchers is one to one which means one voucher can be used only in one order.

##Docker based environment
Develop environment is dockerized and provide abstraction on the top of host environment.
Provided docker services are

- Nginx server
- Mysql 8 server
- PHP FPM with php 8.1

The rest app use Symfony version 6.2

## Set up docker environment
The host system should have docker engine installed to the docker containers.

Before building the containers some environment variable need to be set. Copy .env.example to .env file and set
the following docker environment variables

```
DB_DATABASE=db_name
DB_USERNAME=db_user
DB_PASSWORD=db_password
```

To build docker containers navigate to the main app folder and run following command

```
docker-compose up -d
```

## Composer install

When the docker containers are built, run ``` composer install ``` in the php-fpm container.
To execute the above command in the container run 

```
docker exec -it app8 /bin/bash
```

That will create a bash session in app8 container which is the php-fpm container

### Database migration and fixtures

Database migration will create the database structure with tables required for the entities. Run migration
using doctrine migration console command

```
php bin/console doctrine:migrations:migrate
```

Database fixtures are optional. The can provide fake data to seed the database and test the api services.
To load the fixtures, run the following command 

```
bin/console doctrine:fixtures:load
```

### Postman collection with the available request

To test implemented api service, use postman and provided collection with requests for orders and voucher services. 
Collection provides CRUD request for vouchers and create and list requests for orders.
The collection filename is ```GlobalSavingsGroup.postman_collection.json```

## Author
[Petar Dimov](https://github.com/peterpcm3)
