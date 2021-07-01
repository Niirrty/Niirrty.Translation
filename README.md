# Niirrty.Translation

A small PHP file based translation library.

## Installation

inside the `composer.json`:

```json
{
   "require": {
      "php": ">=7.1",
      "niirrty/niirrty.translation": "~0.3"
   }
}
```

## How to use

```php
include __DIR__ . '/vendor/autoload.php';

use \Niirrty\Locale\Locale;
use \Niirrty\Translation\Sources\PHPFileSource;
use \Niirrty\Translation\Translator;

try
{
    // Init the locale
    $locale = Locale::Create(
        // The fallback locale if no other was found
        new Locale( 'de', 'de', 'UTF-8' ),
        // Check also the URL path for an locale or language part?
        true,
        // This are the names of the parameters, accepted from $_POST, $_GET and $_SESSION
        [ 'lc', 'locale', 'language', 'lang', 'lng' ]
    );

    // Init the translator instance with defined locale
    $translator = new Translator( $locale );

    // Define the templates source directory. There is also a JSONFileSource and a XMLFileSource
    $translatorSource = new PHPFileSource( __DIR__ . '/translations', $locale );
    // ...and add the source to template engine
    $translator->addSource( $translatorSource );

    // If you want access the Translator instance globally
    $translator->setAsGlobalInstance();

    // Example output
    echo Translator::GetInstance()->read( 'Hello1', 'MyApp', 'Hello people!' ), "\n",
         $translator->read( 'NiceTM', 'MyApp', 'NiceToMeetYouM!' );

}
catch ( \Throwable $ex )
{
    // TODO: Do some error handling
}
```

## Translation sources

A translator must have 1 or more translation sources. Each source defines a directory, that must contain the translation
source files, for all supported languages.

### Translation files

* The **file name** (without extension) must be the locale name. (eg. `de` or `de_AT` )
* The **file extension** must be supported by the used `ISource` implementation. Currently known is `.php` (PHPFileSource)
  `.json` (JSONFileSource) and `.xml` (XMLFileSource)
  
### Example directory structure

```
+ translations
   - de.php
   - de_DE.php
   - en.php
   - fr_FR.php 
```

### PHP file source

Format is:

```php
<?php

return [
    'Text 1' => 'Ein Beispieltext',
    'Text 2' => 'Ein anderer Beispieltext',
    'weekdaysList' => [ 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag' ],
    'weekdaysDict' => [ 'Mo' => 'Montag', 'Di' => 'Dienstag', 'Mi' => 'Mittwoch', 'Do' => 'Donnerstag',
                        'Fr' => 'Freitag', 'Sa' => 'Samstag', 'So' => 'Sonntag' ]
];
```

### JSON file source

Format is:

```json
{
    "Text 1"  : "Ein Beispieltext",
    "Text 2"  : "Ein anderer Beispieltext",
    "weekdaysList": [ "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag" ],
    "weekdaysDict": { "Mo": "Montag", "Di": "Dienstag", "Mi": "Mittwoch", "Do": "Donnerstag",
                      "Fr": "Freitag", "Sa": "Samstag", "So": "Sonntag" }
}
```

### XML file source

Format is:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<translations>
   <trans id="Text 1" text="Ein Beispieltext" />
   <trans id="Text 2">Ein anderer Beispieltext</trans>
   <trans id="Text 3">
      <text>Ein ganz anderer Beispieltext</text>
   </trans>
   <trans>
      <id>Text 4</id>
      <text>Hallo Welt</text>
   </trans>
   <trans id="weekdaysList">
      <list>
         <item>Montag</item>
         <item>Dienstag</item>
         <item>Mittwoch</item>
         <item>Donnerstag</item>
         <item>Freitag</item>
         <item>Samstag</item>
         <item>Sonntag</item>
      </list>
   </trans>
   <trans>
      <id>weekdaysDict</id>
      <dict>
         <item key="Mo">Montag</item>
         <item key="Di">Dienstag</item>
         <item key="Mi">Mittwoch</item>
         <item key="Do">Donnerstag</item>
         <item key="Fr">Freitag</item>
         <item key="Sa">Samstag</item>
         <item key="So">Sonntag</item>
      </dict>
   </trans>
</translations>
```
