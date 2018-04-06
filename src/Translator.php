<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Niirrty
 * @package        Niirrty\Translation
 * @since          2017-11-01
 * @version        0.2.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation;


use Niirrty\Locale\Locale;
use Niirrty\Translation\Sources\ISource;


/**
 * The translator.
 *
 * It gets the translation data from specified translation sources.
 *
 * Each translator can use one or more translation sources. Each package/library should use a own source with a
 * unique name. All UK\* packages with a translation requirement uses source names prefixed with '_uk.'
 *
 * @since v0.1.0
 */
class Translator implements ITranslator
{


   // <editor-fold desc="// – – –   P R I V A T E   S T A T I C   F I E L D S   – – – – – – – – – – – – – – – – –">

   /**
    * @type \Niirrty\Translation\Translator
    */
   private static $_instance = null;

   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   F I E L D S   – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * All available sources
    *
    * @type \Niirrty\Translation\Sources\ISource[]
    */
   protected $_sources;

   /** @type \Niirrty\Locale\Locale */
   protected $_locale;

   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   C O N S T A N T S   – – – – – – – – – – – – – – – – – – –">

   protected const USS = '!?§=$)%(&/>_<-@';

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * Translator constructor.
    *
    * @param null|\Niirrty\Locale\Locale $locale
    * @throws \Niirrty\Translation\TranslationException
    */
   public function __construct( ?Locale $locale = null )
   {

      $this->_sources = [];

      if ( null === $locale )
      {
         if ( ! Locale::HasGlobalInstance() )
         {
            throw new TranslationException(
               'Can not init a translator if no usable Locale instance is avialable!'
            );
         }
         $locale = Locale::GetGlobalInstance();
      }

      $this->_locale = $locale;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Gets the source with defined name or NULL.
    *
    * @param string $sourceName
    * @return \Niirrty\Translation\Sources\ISource
    */
   public final function getSource( string $sourceName ) : ?ISource
   {

      return $this->_sources [ $sourceName ] ?? null;

   }

   /**
    * Adds a source with an associated name.
    *
    * @param string                               $sourceName The unique source name
    * @param \Niirrty\Translation\Sources\ISource $source
    * @return \Niirrty\Translation\Translator
    */
   public function addSource( string $sourceName, ISource $source ) : Translator
   {

      $source->setLocale( $this->_locale );

      $this->_sources[ $sourceName ] = $source;

      return $this;

   }

   /**
    * Removes a source
    *
    * @param string $sourceName The source name
    * @return \Niirrty\Translation\Translator
    */
   public function removeSource( string $sourceName ) : Translator
   {

      unset ( $this->_sources[ $sourceName ] );

      return $this;

   }

   /**
    * Removes all sources.
    *
    * @return \Niirrty\Translation\Translator
    */
   public function cleanSources() : Translator
   {

      $this->_sources = [];

      return $this;

   }

   /**
    * Reads the translation and return it.
    *
    * The returned translation can be of each known type, depending to the requirements.
    *
    * If a valid source index is defined, only this source is used.
    *
    * @param string|int  $identifier The translation identifier
    * @param string|null $sourceName The name of the source or NULL for search all sources
    * @param mixed       $defaultTranslation Is returned if the translation was not found
    * @return mixed
    */
   public function read( $identifier, ?string $sourceName = null, $defaultTranslation = false )
   {

      if ( null !== $sourceName && isset( $this->_sources[ $sourceName ] ) )
      {
         // read from specific source
         return $this->_sources[ $sourceName ]->read( $identifier, $defaultTranslation );
      }

      foreach ( $this->_sources as $source )
      {
         $result = $source->read( $identifier, static::USS );
         if ( static::USS !== $result )
         {
            return $result;
         }
      }

      return $defaultTranslation;

   }

   /**
    * Gets a array with all defined sources. THe keys are the associated source names.
    *
    * @return array
    */
   public function getSources() : array
   {

      return $this->_sources;

   }

   /**
    * Gets a iterator over all defined sources.
    *
    * @return \Generator
    */
   public function getSourcesIterator() : \Generator
   {

      foreach ( $this->_sources as $name => $source )
      {
         yield $name => $source;
      }

   }

   /**
    * Gets if one or more sources are defined.
    *
    * @return bool
    */
   public function hasSources() : bool
   {

      return 0 < \count( $this->_sources );

   }

   /**
    * Gets if a source with defined name exists.
    *
    * @param string $sourceName
    * @return bool
    */
   public function hasSource( string $sourceName ) : bool
   {

      return isset( $this->_sources[ $sourceName ] );

   }

   /**
    * Return how many sources currently are defined.
    *
    * @return int
    */
   public function countSources() : int
   {

      return \count( $this->_sources );

   }

   /**
    * Gets the names of all defined sources.
    *
    * @return array
    */
   public function getSourceNames() : array
   {

      return \array_keys( $this->_sources );

   }

   /**
    * Sets the current instance as global usable Translator instance.
    */
   public final function setAsGlobalInstance()
   {

      self::$_instance = $this;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   S T A T I C   M E T H O D S   – – – – – – – – – – – – – – – – –">

   /**
    * Gets if a global instance is defined.
    *
    * @return bool
    */
   public static function HasInstance() : bool
   {

      return null !== self::$_instance;

   }

   /**
    * Gets the global Translator instance. If none is define a empty one is created.
    *
    * @return \Niirrty\Translation\Translator
    * @throws TranslationException
    */
   public static function GetInstance() : Translator
   {

      if ( null === self::$_instance )
      {
         self::$_instance = new Translator();
      }

      return self::$_instance;

   }

   /**
    * Removes the global translator instance.
    */
   public static function RemoveInstance()
   {

      self::$_instance = null;

   }

   // </editor-fold>


}

