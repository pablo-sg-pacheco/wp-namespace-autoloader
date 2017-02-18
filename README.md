# wp-namespace-autoloader
A PHP autoloader class that follows the WordPress coding standards applying PSR-4 specification

**Description**
=====================
Namespaces and autoloaders are cool and help organizing your code. With these features you don't have to worry about including and requiring php files manually ever again and your code gets organized in folders.

This is a [PSR-4](http://www.php-fig.org/psr/psr-4/) autoloader implementation following the specifications of WordPress naming conventions [WordPress naming conventions](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#naming-conventions)

To achieve this I'm following [WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#naming-conventions). It means I'm doing these things:
* Converting classes filenames to lowercase 
* Replacing underscores on class filenames by hyphens
* Putting 'class-' before the final class name

**Note**
-------------
* I know WordPress still gives support to php 5.2 which doesn't have the namespace feature. I'm considering all developers should be using at least php 5.3 here. 


**Installation**
=====================
You just have to require it just like a composer default dependency:

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
use Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;
$autoloader = new \WP_Namespace_Autoloader( array(    
	'directory'          => __DIR__,       // Directory of your project. It can be your theme or plugin. __DIR__ is probably your best bet. 	
	'namespace_prefix'   => __NAMESPACE__, // Main namespace of your project. E.g My_Project\Admin\Tests should be My_Project. Probably if you just pass the constant __NAMESPACE__ it should work		
	'classes_dir'        => 'src',         // (optional). It is where your namespaced classes are located inside your project. If your classes are in the root level, leave this empty. If they are located on 'src' folder, write 'src' here 
) );
$autoloader->init();
```

And now you are good to go. **Now comes the cool part!**
If you have this class
```php
<?php
namespace My_Project\Admin_Pages;
class Main_Page{
}
```
located in **your_projct_root_folder\Admin_Pages\class-main-page.php**, 
you can instantiate it like this:
```php
<?php
new \My_Project\Admin_Pages\Main_Page();
```

If you want to keep all your folders lowercased for some reason, you can use the parameter **'force_to_lowercase'** like this
```php
<?php
namespace My_Project;
new \WP_Namespace_Autoloader( array(    
	'directory'              => __DIR__,
	'force_to_lowercase'     => true,
	'namespace'              => __NAMESPACE__, 
	'classes_dir'            => '',
) );
```
So this is going to be the final path: **your_projct_root_folder\admin_pages\class-main-page.php**, 
