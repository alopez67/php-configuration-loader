Configuration Loader is a class allowing you to browse a config directory and easily get your PHP configuration files values.

## Installation

Just use the following command line:
```
composer require alopez/php-configuration-loader
```

## Basic Usage

There are three classes to know in this package:

- `ConfigurationLoader`: the main class, that will load and return your data.
- `SingleConfiguration`: a simple container for your values.
- `GroupConfiguration`: a container which can be named and contain multiple SingleConfiguration objects.

### Configuration Objects

Both Single and Group Configurations have two properties: `name` and `value`.
The only difference between those two classes is that GroupConfiguration is made for containing multiple SingleConfiguration definitions, see example below:

```php
    // A random configuration file lost in your directories

    $name = 'foo';
    $value = 'fooValue';

    return new \ConfigurationLoader\SingleConfiguration($name, $value);
```

If you have multiple settings (ex: many databases access), there will be an interest into declaring a GroupConfiguration:


```php
    // Databases definitions

    use ConfigurationLoader\GroupConfiguration;
    use ConfigurationLoader\SingleConfiguration;

    $name = 'db';

    return new GroupConfiguration($name,
        [
            new SingleConfiguration('database1',
                [
                    'host' => 'bla',
                    'user' => 'bla',
                    'password' => 'bla',
                    'bla' => 'bla'
                ]
            ),
            new SingleConfiguration('database2',
                [
                    'host' => 'bla2',
                    'user' => 'bla2',
                    'password' => 'bla2',
                    'bla' => 'bla2'
                ]
            )
        ]
    );
```

### Configuration Loader

The configuration loader can easily be declared without any parameters.
You can specify your configuration directory, and precise a flag (true by default) to tell the instance to automatically analyze the entire directory. If this flag is set to "false", configuration directory's data will be available through `get()` method (see example below). Otherwise you'll have to load it through the `load` method.

Example with a SingleConfiguration instance:

```php
    $loader = new \ConfigurationLoader\ConfigurationLoader($configPath, $lazyOrNot);

    // The path must be considering that the current directory is your conf folder,
    // You must not precise '.php' extension.
    $filepath = 'path/to/your/file/without/.extension';

    // If not lazy you have to load the file (it will also return the values you want to get)
    // Let's say you want to load the file containing our newly created 'foo' SingleConfiguration:
    $data = $loader->load($filepath);

    // will echo 'fooValue'
    var_dump($data);

    // Once data is already load you can use get in order to get the configuration
    // It also will return 'fooValue'
    $data = $loader->get('foo')
```

With a GroupConfiguration instance (assuming loader is already declared as lazy):

```php
    $loader->load('dbconf');
    $data = $loader->get('db');

    var_dump($data);

    // var_dump will return a formatted array with the following format:
    array(
        'database1' => array(...),
        'database2' => array(...)
    )
```

**Notice**: If you declared the loader with the lazy flag on `true`, but still want to load data later, you can use the method `loadDirectory` which will load the directory specified in your constructor.

### Handling local files

The loader is able to differentiate `.local.php` files from `.php`.
If you create `foo.php` and `foo.local.php`, the `load` method will first take the `foo.local.php` content and ignore `foo.php`.

```php
    // foo.php
    return new \ConfigurationLoader\SingleConfiguration('foo', 'foo');

    // foo.local.php
    return new \ConfigurationLoader\SingleConfiguration('foo', 'bar');

    // index.php
    $loader = new \ConfigurationLoader\ConfigurationLoader($configPath);
    $loader->load('foo');

    // will return 'bar'
    var_dump($loader->get('foo'));
```
