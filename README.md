naucon Storage Package
======================

## About

This package is an abstraction layer for key/value Stores (storages). The goal is to decouple a concrete key/value store from the business logic.

### Features

* storage manager as abstraction layer for storages or stores with basic CRUD operations.
* storage factory and registry to manage multiple storages or stores.
* chained storages to save entities in multiple storages or stores.
* support composite identifiers / keys.


### Adapters

* array (in-memory)
* file system
* native session
* bridge to symfony session handler
* redis (predis) - requires redis 2.8 or higher
* doctrine ORM
* PSR-6 Cache
* PSR-16 Simple Cache
* null storage (stores nothing, no-memory)


### Compatibility

* PHP 7.1 to 7.4

## Installation

install the latest version via composer

```bash
    composer require naucon/storage
```


## Usage

### Getting started

To use the storage we need a model (plain-old php objects). This model will be serialized so it is a good idea that the model implements the `Serializable` interface - even if it is not mandatory.

```php
    class Product implements \Serializable
    {
        protected $id;
        protected $sku;
        protected $description;
    
        public function getId()
        {
            return $this->id;
        }
    
        public function setId($id)
        {
            $this->id = $id;
        }
    
        public function getSku()
        {
            return $this->sku;
        }
    
        public function setSku($sku)
        {
            $this->sku = $sku;
        }
    
        public function getDescription()
        {
            return $this->description;
        }
    
        public function setDescription($description)
        {
            $this->description = $description;
        }
    }
```

With a defined model we are able to create a `StorageManager`. The `StorageManager` provides all the basic CRUD operations `create()`, `has()`, `find()`, `flush()`, `remove()`.
To work it requires a storage provider. The provider implements a concrete storage or store. In this example we use the `ArrayStorage` but you can choose any other supported provider or build a custom storage to your needs.

```php
    use Naucon\Storage\Provider\ArrayStorage;
    use Naucon\Storage\StorageManager;
        
    $adapter = new ArrayStorage(Product::class);
    $manager = new StorageManager($adapter);
```

To save an entry in the storage we call `flush($identifier, $model)`

```php
    $model = new Product();
    $model->setId(1);
    $model->setSku('U123');
    $model->setDescription('Dragon fruit');

    $manager->flush(1, $model);
```

To create a new instance of the model you can call `StorageManager::create()`. Requires a storage that implements `CreateAwareInterface`. The created instance will not be in the storage until you `flush()` it.

```php
    $model = $manager->create();
    $model->setId(1);
    $model->setSku('U123');
    $model->setDescription('Dragon fruit');

    $manager->flush(1, $model);
```

To retrieve an entry from the storage we call `StorageManager::find($identifier)`. If no entry was found you get `null` in return.
To verify if a storage contains an entry you can call `StorageManager::has($identifier)`.

```php
    if ($manager->has(1)) {
        $model = $manager->find(1);
    }
```

To always get a model instance you can call `StorageManager::findOrCreate($identifier)`.  That will create a new model instance if no entry was found. Requires a storage that implements `CreateAwareInterface`.
The created instance will not be in the storage until you `flush()` it.

```php
    $model = $manager->findOrCreate(1);
```

To remove an entry from storage you can call `StorageManager::remove($identifier, $model)`.

```php
    $manager->remove(1, $product);
```

Remove all entries from the storage with `StorageManager::removeAll()`.

```php
    $models = $manager->removeAll();
```

With `StorageManager::findAll()` you can retrieve all entries from the storage.

```php
    $models = $manager->findAll();
```

With `StorageManager::findMultiple(array $identifiers)` you can retrieve multiple entries from the storage.

```php
    $models = $manager->findMultiple([1, 2]);
```

### Doctrine ORM

The identifier must be handled by the storage component. Therefor doctrine ORM must use "NONE" as a Identifier Generation Strategies

XML

```xml
    <doctrine-mapping>
      <entity name="Product">
        <id name="id" type="integer">
            <generator strategy="NONE" />
        </id>
      </entity>
    </doctrine-mapping>
```

YAML

```yaml
    Product:
      type: entity
      id:
        id:
          type: integer
          generator:
            strategy: NONE
```

Annotation

```php
    <?php
    class Product
    {
        /**
         * @Id
         * @GeneratedValue(strategy="NONE")
         */
        protected $id = null;
    }
```

### Advanced Usage

#### Composite Identifiers / keys

The Identifier can be an integer, string or array of composite identifiers (keys).

```php
    $manager->flush(['product_id' => 1, 'variation_id' => 4], $model);
```

A composite identifier has to follow the format of the upper example.

#### StorageChain

Use the `StorageChain` to save an entry in multiple storages at the same time.

```php
    $storageInMemory = new ArrayStorage(Product::class);
    $storageSession  = new NativeSessionStorage(Product::class);
    
    $storageChain = new StorageChain();
    $storageChain->register('product_default', $storageInMemory);
    $storageChain->register('product_session', $storageSession);
```

The `StorageChain` provides all the basic CRUD operations `has()`, `find()`, `flush()`, `remove()`. Except `create()` and `findOrCreate()` these two methods are not supported.


#### StorageFactory

To manage multiple storages and/or different models you can use the `StorageFactory`.

```php
    $storage = new ArrayStorage(Product::class);
    
    $storageFactory = new StorageFactory();
    $storageFactory->register('product', $storage);
    
    $storage = $storageFactory->getStorage('product');
```

#### Combining Everything

Because the `StorageManager` and `StorageChain` implementing the same `StorageInterface` as the concrete storage provide you can combine them.

```php
    $storageInMemory = new ArrayStorage(Product::class);
    $storageSession  = new NativeSessionStorage(Product::class);
    
    $storageChain = new StorageChain();
    $storageChain->register('product_default', $storageInMemory);
    $storageChain->register('product_session', $storageSession);
    
    $storageManager = new StorageManager($storageChain);

    $storageFactory = new StorageFactory();
    $storageFactory->register('product', $storageManager);
```

## Example

Start the build-in webserver to see the examples in action:

```bash
    cd examples
    php -S 127.0.0.1:3000
```

open url in browser

```php
    http://127.0.0.1:3000/index.html
```

For the doctrine example please have a look at [README.md](examples/doctrine/README.md) in the `example/doctrine` directory.

## License

The MIT License (MIT)

Copyright (c) 2015 Sven Sanzenbacher

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
