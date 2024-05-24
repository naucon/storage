# Doctrine Example

## Setup

first create the database schema on the command line

```bash
    cd examples/doctrine
    php ../../vendor/bin/doctrine orm:schema-tool:create
```

to update schema

```bash
    php ../../vendor/bin/doctrine orm:schema-tool:drop --force
    php ../../vendor/bin/doctrine orm:schema-tool:create
```

or

```bash
    php ../../vendor/bin/doctrine orm:schema-tool:update --force
```

## Run Example

### CLI

```bash
    php DoctrineStorageExample.php
```

### Webserver

Start the build-in webserver to see the examples in action:

```bash
    cd examples
    php -S 127.0.0.1:3000
```

open url in browser

```php
    http://127.0.0.1:3000/index.html
```
