<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Niirrty
 * @package        Niirrty\Translation\Sources
 * @since          2017-11-01
 * @version        0.2.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use Niirrty\Locale\Locale;
use Psr\Log\LoggerInterface;


/**
 * Each translation source must implement this interface.
 *
 * @since v0.1.0
 */
interface ISource
{

   /**
    * Reads one or more translation values.
    *
    * @param  string|int $identifier
    * @param  mixed      $defaultTranslation Is returned if no translation was found for defined identifier.
    * @return mixed
    */
   public function read( $identifier, $defaultTranslation = false );

   /**
    * Gets the current defined locale.
    *
    * @return \Niirrty\Locale\Locale
    */
   public function getLocale() : Locale;

   /**
    * Gets the current defined logger.
    *
    * @return \Psr\Log\LoggerInterface
    */
   public function getLogger() : LoggerInterface;

   /**
    * Gets all options of the translation source.
    *
    * @return array
    */
   public function getOptions() : array;

   /**
    * Gets the option value of option with defined name or FALSE if the option is unknown.
    *
    * @param string $name The name of the option.
    * @param mixed  $defaultValue This value is remembered and returned if the option not exists
    * @return mixed
    */
   public function getOption( string $name, $defaultValue = false );

   /**
    * Sets a new locale.
    *
    * @param \Niirrty\Locale\Locale $locale
    * @return \Niirrty\Translation\Sources\ISource
    */
   public function setLocale( Locale $locale );

   /**
    * Sets a new logger or null if no logger should be used.
    *
    * @param \Psr\Log\LoggerInterface|null $logger
    * @return \Niirrty\Translation\Sources\ISource
    */
   public function setLogger( ?LoggerInterface $logger );

   /**
    * Sets a options value.
    *
    * @param string $name
    * @param $value
    * @return \Niirrty\Translation\Sources\ISource
    */
   public function setOption( string $name, $value );

   /**
    * Gets if an option with defined name exists.
    *
    * @param string $name The option name.
    * @return bool
    */
   public function hasOption( string $name ) : bool;

   /**
    * Reload the source by current defined options.
    *
    * @return \Niirrty\Translation\Sources\ISource
    */
   public function reload();


}

