# wp-namespace-autoloader
A PHP autoloader class that follows the WordPress coding standards for class names and class filenames but uses **Namespace** feature from php 5.3

**Installation**
=====================
You just have to require it just like a default composer dependency:

```json
"require": {	
	"pablo-pacheco/wp-namespace-autoloader": "dev-master"
}
```

**Usage**
===============
Firstly, load the composer dependencies like you are used to

```php
<?php
require __DIR__ . '/vendor/autoload.php';
```

Now you have to initialize it

```php
<?php
new \WP_Namespace_Autoloader( array(    
	'directory'   => __DIR__,       // Directory of your project. It can be your theme or plugin. __DIR__ is prbably your best bet. 	
	'namespace'   => __NAMESPACE__, // Namespace of your base project. E.g My_Project\Admin\Tests should be My_Project. Probably if you just pass the constant __NAMESPACE__ it should work		
	'classes_dir' => 'src',         // (optional). It is where your namespaced classes are located inside your project. If your classes are in the root level, leave this empty. If they are located on 'src' folder, write 'src' here 
) );
?>
```
