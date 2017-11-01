<?php

include \dirname( __DIR__ ) . '/vendor/autoload.php';

$translatorDE = new \Niirrty\Translation\Translator( new \Niirrty\Locale\Locale( 'de', 'DE' ) );
$translatorEN = new \Niirrty\Translation\Translator( new \Niirrty\Locale\Locale( 'en', 'UK' ) );
$translatorFR = new \Niirrty\Translation\Translator( new \Niirrty\Locale\Locale( 'fr', 'FR' ) );

$translatorDE->addSource( '_', new \Niirrty\Translation\Sources\PHPFileSource( __DIR__ . '/translations' ) );
$translatorEN->addSource( '_', new \Niirrty\Translation\Sources\PHPFileSource( __DIR__ . '/translations' ) );
$translatorFR->addSource( '_', new \Niirrty\Translation\Sources\PHPFileSource( __DIR__ . '/translations' ) );

echo  '"',
      $translatorEN->read( 'A example text', '_' ),
      '" is in german "',
      $translatorDE->read( 'A example text' ),
      '"', "\n";

print_r( $translatorEN->read( 'weekdays' ) );
print_r( $translatorDE->read( 'weekdays' ) );
print_r( $translatorFR->read( 'weekdays', '_',
                              [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ] ) );

