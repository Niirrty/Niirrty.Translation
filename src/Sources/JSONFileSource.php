<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-04-03
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use Niirrty\IO\Vfs\Manager;
use Niirrty\Locale\Locale;
use Psr\Log\LoggerInterface;


class JSONFileSource extends AbstractSource
{


   // <editor-fold desc="// –––––––   C O N S T R U C T O R   A N D / O R   D E S T R U C T O R   ––––––––">

   /**
    * XMLFileSource constructor.
    *
    * @param string                        $folder
    * @param \Niirrty\Locale\Locale        $locale
    * @param \Niirrty\IO\Vfs\Manager|null  $vfsManager
    * @param null|\Psr\Log\LoggerInterface $logger
    */
   public function __construct(
      string $folder, Locale $locale, ?Manager $vfsManager = null, ?LoggerInterface $logger = null )
   {

      parent::__construct( $locale, $logger );

      $this->_options[ 'folder'     ] = $folder;
      $this->_options[ 'vfsManager' ] = $vfsManager;

      $this->_log->info( 'Init JSON file translation source for folder "' . $folder . '".', [ 'Class' => __CLASS__ ] );

   }

   // </editor-fold>


   // <editor-fold desc="// –––––––   P U B L I C   M E T H O D S   ––––––––––––––––––––––––––––––––––––––">

   /**
    * Sets a new array with translation data that should be used.
    *
    * The array keys are the identifiers (string|int) the values must be arrays with items 'text' and optionally
    * with 'category' or the values is a string that will be converted to [ 'text' => $value ]
    *
    * @param array $data
    * @param bool  $doReload
    * @return \Niirrty\Translation\Sources\JSONFileSource
    */
   public function setData( array $data, bool $doReload = true )
   {

      $this->_options[ 'data' ] = [];

      foreach ( $data as $key => $value )
      {
         $this->_options[ 'data' ][ $key ] = $value;
      }

      if ( $doReload )
      {
         $this->reload();
      }

      return $this;

   }

   /**
    * Reads one or more translation values.
    *
    * @param  string|int $identifier
    * @param  mixed      $defaultTranslation Is returned if no translation was found for defined identifier.
    * @return mixed
    */
   public function read( $identifier, $defaultTranslation = false )
   {

      if ( ! \is_int( $identifier ) && ! \is_string( $identifier ) )
      {
         // No identifier => RETURN ALL REGISTERED TRANSLATIONS
         return $this->_options[ 'data' ];
      }

      // A known identifier format
      if ( ! isset( $this->_options[ 'data' ][ $identifier ] ) )
      {
         // The translation not exists
         return $defaultTranslation;
      }

      return $this->_options[ 'data' ][ $identifier ];

   }

   /**
    * Reload the source by current defined options.
    *
    * @return \Niirrty\Translation\Sources\JSONFileSource
    */
   public function reload()
   {

      if ( isset( $this->_options[ 'folder' ] ) )
      {
         return $this->reloadFromFolder();
      }

      if ( ! isset( $this->_options[ 'file' ] ) || ! \file_exists( $this->_options[ 'file' ] ) )
      {
         return $this;
      }

      return $this->reloadFromFile();

   }

   /**
    * Sets a options value.
    *
    * @param string $name
    * @param mixed  $value
    * @return \Niirrty\Translation\Sources\JSONFileSource
    */
   public function setOption( string $name, $value )
   {

      parent::setOption( $name, $value );

      return $this;

   }

   private function getVfsManager() : Manager
   {

      return $this->_options[ 'vfsManager' ];

   }

   private function hasVfsManager() : bool
   {

      return null !== $this->_options[ 'vfsManager' ];

   }


   // </editor-fold>


   // <editor-fold desc="// –––––––   P R I V A T E   M E T H O D S   ––––––––––––––––––––––––––––––––––––">

   /**
    * @return \Niirrty\Translation\Sources\JSONFileSource
    */
   private function reloadFromFolder()
   {

      $languageFolderBase = $this->_options[ 'folder' ];

      if ( $this->hasVfsManager() )
      {
         $languageFolderBase = $this->getVfsManager()->parsePath( $languageFolderBase );
      }

      $languageFolderBase = \rtrim( $languageFolderBase, '\\/' );

      if ( ! empty( $languageFolderBase ) ) { $languageFolderBase .= '/'; }

      $languageFile = $languageFolderBase . $this->_locale->getLID() . '_' . $this->_locale->getCID();

      if ( \strlen( $this->_locale->getCharset() ) > 0 )
      {
         $languageFile .= '/' . $this->_locale->getCharset() . '.json';
      }
      else
      {
         $languageFile .= '.json';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         $languageFile = $languageFolderBase . $this->_locale->getLID() . '_' . $this->_locale->getCID() . '.json';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         $languageFile = $languageFolderBase . $this->_locale->getLID() . '.json';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         unset(
            $this->_options[ 'file' ],
            $this->_options[ 'folder' ]
         );
         return $this;
      }

      $this->_options[ 'file' ]   = $languageFile;
      $this->_options[ 'folder' ] = $languageFolderBase;

      return $this->reloadFromFile();

   }

   /**
    * @return \Niirrty\Translation\Sources\JSONFileSource
    */
   private function reloadFromFile()
   {

      $this->_log->info( 'Reload data from file "' . $this->_options[ 'file' ] . '".', [ 'Class' => __CLASS__ ] );

      try
      {
         $translations = $this->parseJSON( \file_get_contents( $this->_options[ 'file' ] ) );
      }
      catch ( \Throwable $ex )
      {
         $this->_log->notice( 'Unable to loaf translations file. ' . $ex->getMessage(), [ 'Class' => __CLASS__ ] );
         $translations = [];
      }

      if ( ! \is_array( $translations ) )
      {
         $translations = [];
      }

      if ( ! isset( $this->_options[ 'data' ] ) )
      {
         $this->_options[ 'data' ] = [];
      }

      return $this->setData( \array_merge( $this->_options[ 'data' ], $translations ), false );

   }

   private function parseJSON( string $jsonData ) : array
   {

      $out = \json_decode( $jsonData, true );

      return $out;

   }

   // </editor-fold>


}

