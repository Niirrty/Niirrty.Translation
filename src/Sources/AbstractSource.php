<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Niirrty
 * @package        Niirrty\Translation\Sources
 * @since          2017-11-01
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use Niirrty\Locale\Locale;


/**
 * Defines a abstract ISource implementation.
 *
 * @since v0.1.0
 */
abstract class AbstractSource implements ISource
{


   // <editor-fold desc="// – – –   P R O T E C T E D   F I E L D S   – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Declares if the source is valid for reading.
    *
    * @type bool
    */
   protected $_isValid;

   /**
    * The locale that should be used by Source
    *
    * @type \Niirrty\Locale\Locale
    */
   protected $_locale;

   /**
    * All options of the Source implementation
    *
    * @type array
    */
   protected $_options      = [];

   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   C O N S T R U C T O R   – – – – – – – – – – – – – – – – –">

   /**
    * AbstractSource constructor.
    */
   protected function __construct()
   {

      $this->_isValid = false;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Gets if the current source is valid for reading.
    *
    * @return bool
    */
   public final function isValid() : bool
   {

      return $this->_isValid && null !== $this->_locale;

   }

   /**
    * Gets the current defined locale.
    *
    * @return \Niirrty\Locale\Locale
    */
   public final function getLocale() : Locale
   {

      return $this->_locale;

   }

   /**
    * Sets a new locale.
    *
    * @param \Niirrty\Locale\Locale $locale
    * @return \Niirrty\Translation\Sources\ISource
    */
   public function setLocale( Locale $locale )
   {

      $this->_locale = $locale;

      return $this->reload();

   }

   /**
    * Gets all options of the translation source.
    *
    * @return array
    */
   public final function getOptions() : array
   {

      return $this->_options;

   }

   /**
    * Gets the option value of option with defined name or FALSE if the option is unknown.
    *
    * @param string $name The name of the option.
    * @param mixed  $defaultValue This value is remembered and returned if the option not exists. If the value is NULL
    *                             the value is not set, it is only returned in this case.
    * @return mixed
    */
   public final function getOption( string $name, $defaultValue = false )
   {

      if ( ! $this->hasOption( $name ) )
      {
         if ( null === $defaultValue )
         {
            return $defaultValue;
         }
         $this->_options[ $name ] = $defaultValue;
      }

      return $this->_options[ $name ];

   }

   /**
    * Gets if an option with defined name exists.
    *
    * @param string $name The option name.
    * @return bool
    */
   public final function hasOption( string $name ) : bool
   {

      return \array_key_exists( $name, $this->_options );

   }

   /**
    * Sets a options value.
    *
    * @param string $name
    * @param mixed  $value
    * @return \Niirrty\Translation\Sources\AbstractSource
    */
   public function setOption( string $name, $value )
   {

      $this->_options[ $name ] = $value;

      return $this;

   }

   // </editor-fold>


}

