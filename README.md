# Blog demo project

## Setup

Make sure you have [VirtualBox](https://www.virtualbox.org/wiki/Downloads) and [Vagrant](https://developer.hashicorp.com/vagrant/install?product_intent=vagrant) installed.
Then follow instructions to install [Laravel Homestead](https://laravel.com/docs/11.x/homestead).

After that, clone this repository and update Homestead configuration file `Homestead.yaml` with the correct path to the project folder.

Example Homestead config file `~/Homestead/Homestead.yaml`:
```yaml
---
ip: "192.168.56.56"
memory: 2048
cpus: 2
provider: virtualbox

authorize: ~/.ssh/id_rsa.pub

keys:
    - ~/.ssh/id_rsa

folders:
    - map: ~/Projects/symfony-blog-demo
      to: /home/vagrant/projects/symfony-blog-demo

sites:
    - map: blog.test
      to: /home/vagrant/projects/symfony-blog-demo/public
      type: "symfony4"
      php: "7.4"

databases:
    - homestead

features:
    - mariadb: false
    - postgresql: false
    - ohmyzsh: false
    - webdriver: false

services:
    - enabled:
          - "mysql"

ports:
    - send: 33060 # MySQL/MariaDB
      to: 3306
    - send: 8025 # Mailpit
      to: 8025
```

Then run the following commands to start the VM and SSH into it (from Homestead directory):
```bash
vagrant up
vagrant ssh
```

Inside the VM, navigate to the project folder and install dependencies:

```bash
cd projects/symfony-blog-demo # or whatever path you have set in Homestead.yaml

composer install

# If needed, copy .env file and update it with correct database credentials
cp .env .env.local

# Create database and run migrations
php bin/console doctrine:database:create 
php bin/console doctrine:migrations:migrate

# Load fixtures (use `--append` to add new fixtures to the existing data)
php bin/console doctrine:fixtures:load --group=default

# Run the messenger worker to process async messages
php bin/console messenger:consume async
```

Now you should be able to access the project in your browser at [http://blog.test](http://blog.test).

## Default credentials

| Role  | Email         | Password |
|-------|---------------|----------|
| Admin | admin@test.com| admin12345 |
| User  | test@test.com | test12345  |

## Stopping the VM

To stop the VM, run the following command from the Homestead directory:

```bash
vagrant halt

# Or to destroy the VM
vagrant destroy
```

## Testing

To prepare the test database, update the `.env.test` file with the correct database credentials and run the following composer script:

```bash
composer prepare-test-db
```

Or manually run the following commands:

```bash
# Drop the test database if it exists
php bin/console --env=test doctrine:database:drop --force --if-exists --quiet

# Create the test database
php bin/console --env=test doctrine:database:create --no-interaction --quiet

# Create the tables/columns in the test database
php bin/console --env=test doctrine:schema:create --quiet

# Load test fixtures
php bin/console --env=test doctrine:fixtures:load --group=test --no-interaction --quiet
```

To run tests, execute the following command:

```bash
php bin/phpunit
```

## Additional commands

```bash
# Run tests
php bin/phpunit

# Clear cache
php bin/console cache:clear

# Run messenger worker to process async messages
php bin/console messenger:consume async

# Retry failed messages
php bin/console messenger:failed:retry

# Update translations
php bin/console translation:extract --force --format=json --prefix='' --domain=messages en
```
