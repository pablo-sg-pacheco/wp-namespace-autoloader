# wp-namespace-autoloader
A PHP autoloader class that follows WordPress coding standards using **Namespace** feature from php 5.3 >

**Description**
=====================
Namespaces and autoloaders are cool and help organizing your code. With these features you don't have to worry about including and requiring php files manually ever again and your code gets organized in folders.

The implementation of this autoloader is practically the same as of a [PSR-4](http://www.php-fig.org/psr/psr-4/) one. The only difference here is that I'm following the WordPress coding standards for the final file to be loaded. 

To achieve this I'm following [WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#naming-conventions). It means I'm doing these things:
* Converting all classes and paths to lowercase 
* Replacing underscores on class names by hyphens
* Putting 'class-' before the final class name

**Note**
-------------
* I'm also replacing the main project namespace by an emtpy string. It means that it shouldn't be considered on autoload. See the example to understand it better
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
new \WP_Namespace_Autoloader( array(    
	'directory'   => __DIR__,       // Directory of your project. It can be your theme or plugin. __DIR__ is probably your best bet. 	
	'namespace'   => __NAMESPACE__, // Main namespace of your project. E.g My_Project\Admin\Tests should be My_Project. Probably if you just pass the constant __NAMESPACE__ it should work		
	'classes_dir' => 'src',         // (optional). It is where your namespaced classes are located inside your project. If your classes are in the root level, leave this empty. If they are located on 'src' folder, write 'src' here 
) );
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

If you want to keep all your folders lowercased for some reason, you can use the parameter **'namespace_to_lowercase'** like this
```php
<?php
namespace My_Project;
new \WP_Namespace_Autoloader( array(    
	'directory'              => __DIR__,
	'namespace_to_lowercase' => true,
	'namespace'              => __NAMESPACE__, 
	'classes_dir'            => '',
) );
```
So this is going to be the final path: **your_projct_root_folder\admin_pages\class-main-page.php**, 
