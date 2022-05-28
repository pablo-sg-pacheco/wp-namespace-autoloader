# wp-namespace-autoloader
A PHP autoloader class that follows the WordPress coding standards 2.0 applying PSR-4 specification and optionally supports proposed 3.0 

**Description**
=====================
Namespaces and autoloaders are cool and help organizing your code. With these features you don't have to worry about including and requiring php files manually ever again and your code gets organized in folders.

This is a [PSR-4](http://www.php-fig.org/psr/psr-4/) autoloader implementation following [WordPress naming conventions 2.0](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#naming-conventions) and proposed [WordPress naming conventions 3.0](https://make.wordpress.org/core/2020/03/20/updating-the-coding-standards-for-modern-php)

It means I'm doing these things:
* Converting classes filenames to lowercase 
* Replacing underscores on class filenames by hyphens
* Prepending 'class-' before the final class name
* [Optional]: Prepending 'interface-' before the final interface name
* [Optional]: Prepending 'trait-' before the final trait name

**Note**
-------------
* Required PHP Version is PHP 5.4 


**Installation**
=====================
You just have to require it just like a composer default dependency. You may have to use **preferred-install** as **dist** so you will be able to commit files as .git files will not be created

```json
"require": {	
	"pablo-sg-pacheco/wp-namespace-autoloader": "dev-master"
},
"config": {
	"preferred-install": "dist"
}
```

**Usage**
===============
Firstly, load the composer dependencies like you are used to

```php
<?php
require __DIR__ . '/vendor/autoload.php';
```

Now you have to initialize it and you are good to go

```php
<?php
use Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;
$autoloader = new WP_Namespace_Autoloader( array(    
	'directory'          => __DIR__,       // Directory of your project. It can be your theme or plugin. Defaults to __DIR__ (probably your best bet). 	
	'namespace_prefix'   => 'My_Project',  // Main namespace of your project. E.g My_Project\Admin\Tests should be My_Project. Defaults to the namespace of the instantiating file.	
	'classes_dir'        => 'src',         // (optional). It is where your namespaced classes are located inside your project. If your classes are in the root level, leave this empty. If they are located on 'src' folder, write 'src' here 
	'prepend_class'      => true,          // (optional). Default true, prepends class- before the final class name 
	'prepend_interface'  => true,          // (optional). Default false, prepends interface- before the final interface name 
	'prepend_trait'      => true,          // (optional). Default false, prepends trait- before the final trait name 
) );
$autoloader->init();
```

**Now comes the cool part!**
If you have a simple class located on **your_projct_root_folder\Admin_Pages\class-main-page.php**
like this, you can instantiate it and it's going to work
```php
<?php
namespace My_Project\Admin_Pages;
class Main_Page{
}

```
Or if you have a simple interface located on **your_projct_root_folder\Admin_Pages\interface-init.php**
```php
<?php
namespace My_Project\Admin_Pages;
interface Init {
}

```
And you have a simple class implementing that interface located on **your_projct_root_folder\Admin_Pages\class-main-page.php**
you can instantiate it and it's going to work

```php
<?php
namespace My_Project\Admin_Pages;
class Main_Page implements Init {
}
```
Or if you have a simple trait located on **your_projct_root_folder\Admin_Pages\trait-my-trait.php**
```php
<?php
namespace My_Project\Admin_Pages;
trait My_Trait {
}
```
And you have a simple class using that trait located on **your_projct_root_folder\Admin_Pages\class-main-page.php**
you can instantiate it and it's going to work
```php
<?php
namespace My_Project\Admin_Pages;
class Main_Page {
    use My_Trait
}
```

**Parameters**
===============

Parameter | Default value | Description
------------ | ------------- | ------------
**directory** | ```null``` | Path of your project. Probably use **```__DIR__```** here
**namespace_prefix** | ```null``` | Namespace prefix of your project
**classes_dir** | ```array( '.', 'vendor' )``` | Relative path of the directory containing all your classes. Accepts string or array of strings. Defaults to **`directory`** parameter and the vendor subdirectory.  **(optional)**.
**lowercase** | ```array('file')``` | If you want to lowercase just the file or folders too. It accepts an array with two possible values: **'file', 'folders'**.
**underscore_to_hyphen** | ```array('file')``` | If you want to convert underscores to hyphens. It accepts an array with two possible values: **'file',  'folders'**.
**prepend_class** | ```true``` | If you want to prepend 'class-' before files
**prepend_interface** | ```false``` | If you want to prepend 'interface-' before files
**prepend_trait** | ```false``` | If you want to prepend 'trait-' before files

This class has parameters that make it flexible enough to fit any kind of project.

**Examples**
* Lowercases all folders using `lowercase => array('file','folders')`
* Converts underscores to hyphens on folders too with `underscore_to_hyphen => array('file','folders')`
* Doesn't prepend class before file with `prepend_class => false`
