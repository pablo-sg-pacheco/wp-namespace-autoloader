# wp-namespace-autoloader
A PHP autoloader class that follows the WordPress coding standards for class names and class filenames but uses **Namespace** feature from php 5.3

**Description**
=====================
I don't know exactly why namespaces and autoloaders aren't used widely in WordPress community but i really think they are cool and help organizing your code. With these features you don't have to worry about including and requiring php files manually ever again and your code gets organized in folders.

To achieve this I'm following [WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#naming-conventions). It means I'm doing these things:
* Converting all classes to lowercase and replacing underscores by hyphen
* Putting 'class-' before the final class name

**Note**
-------------
* I'm also replacing the main project namespace by emtpy string. It means that it shouldn't be considered on autoload. See the example to understand it better
* I know WordPress still gives support to php 5.2 which doesn't have the namespace feature. I'm considering all developers should be using at least php 5.3 here. 


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
```

And now you are good to go

**Example**
===============
**Now it's the cool part!**
If you have this class
```php
<?php
namespace My_Project\Admin_Pages;
class Main_Page{
}
```
located in **your_projct_root_folder\admin_pages\class-main_page.php**, 
you can instantiate it like this:
```php
<?php
new \My_Project\Admin_Pages\Main_Page();
```
