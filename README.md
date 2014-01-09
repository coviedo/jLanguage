jLanguage-PHP Class
==================================

`jLanguage` is a class for implement a multi-language system based in JSON, fast and easy in your web site.

# Installation

__Require the class:__

```php
require_once 'src\jLanguage\jLanguage.php';
```

__Create new instance of class:__

```php
$jLang = new jLanguage(array(
	'lang_default' => 'en' // necessary.
));
```

__Select the sections to use:__

```php
$jLang->set_section('index');
// or
$jLang->set_section(array('index', 'home'));
```

__Create a json file in your folder json data whit the name of language:__
```json
/*en.json*/
{
  "index":
  {
    "hello": "Hello world."
  }
}
```
__In your output file write:__

```
[[hello]]
```

__And jLanguage returns:__

```
Hello world.
```

# Configuration

The configuration is the following concepts:

```php
$config =  array(
		'auto_shutdown' => true, // (bool) Execute a function at the end of the script to translate the text automatically.
		'auto_load' => true, // (bool) Load json files automatically.
		'lang_default' => null, // (string) Language file to be charged.
		'auto_location' => true, // (bool) Automatically load the language of language, as the language of the user's browser.
		'base_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data-lang' // Location of the json files.
	);

$jLang = new jLanguage($config);
```

__Note:__
In case that the shutdown function it's not automatically, you can call this function to get the translated text:

```php
$return = $jLang->return_shutdown();
```

__Note 2:__ In case that the load function it's not automatically, you can load the json files following this code:
```php
$jLang->load();
```