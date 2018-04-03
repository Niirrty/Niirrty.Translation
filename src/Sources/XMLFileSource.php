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
use Niirrty\XmlAttributeHelper;
use Psr\Log\LoggerInterface;


class XMLFileSource extends AbstractSource
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

      $this->_log->info( 'Init XML file translation source for folder "' . $folder . '".', [ 'Class' => __CLASS__ ] );

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
    * @return \Niirrty\Translation\Sources\XMLFileSource
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
    * @return \Niirrty\Translation\Sources\XMLFileSource
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
    * @return \Niirrty\Translation\Sources\XMLFileSource
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
    * @return \Niirrty\Translation\Sources\XMLFileSource
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
         $languageFile .= '/' . $this->_locale->getCharset() . '.xml';
      }
      else
      {
         $languageFile .= '.xml';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         $languageFile = $languageFolderBase . $this->_locale->getLID() . '_' . $this->_locale->getCID() . '.xml';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         $languageFile = $languageFolderBase . $this->_locale->getLID() . '.xml';
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
    * @return \Niirrty\Translation\Sources\XMLFileSource
    */
   private function reloadFromFile()
   {

      $this->_log->info( 'Reload data from file "' . $this->_options[ 'file' ] . '".', [ 'Class' => __CLASS__ ] );

      try
      {
         $translations = $this->parseXML( \simplexml_load_file( $this->_options[ 'file' ] ) );
      }
      catch ( \Throwable $ex )
      {
         $this->_log->notice( 'Unable to include translations file.' . $ex->getMessage(), [ 'Class' => __CLASS__ ] );
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

   private function parseXML( \SimpleXMLElement $xmlDoc ) : array
   {

      $out = [];

      if ( ! isset( $xmlDoc->trans ) )
      {
         return $out;
      }

      $elementIndex = 0;
      foreach ( $xmlDoc->trans as $transElement )
      {
         if ( null === ( $id = $this->findId( $transElement ) ) )
         {
            $this->_log->notice(
               'Parse-Error: Invalid trans element at index ' . $elementIndex . '. Missing a Identifier-Definition.' );
            continue;
         }
         if ( null === ( $txt = $this->findText( $transElement ) ) )
         {
            if ( null === ( $txt = $this->findList( $transElement ) ) )
            {
               if ( null === ( $txt = $this->findDict( $transElement ) ) )
               {
                  $this->_log->notice(
                     'Parse-Error: Invalid trans element at index ' . $elementIndex . '. Missing a Text/List/Dict.' );
                  continue;
               }
            }
         }
         $out[ $id ] = $txt;
      }

      return $out;

   }

   private static function findId( \SimpleXMLElement $transElement ) : ?string
   {

      if ( null !== ( $id = XmlAttributeHelper::GetAttributeValue( $transElement, 'id' ) ) )
      {
         return $id;
      }

      if ( isset( $transElement->id ) )
      {
         return (string) $transElement->id;
      }

      return null;

   }

   private static function findText( \SimpleXMLElement $transElement ) : ?string
   {

      if ( null !== ( $txt = XmlAttributeHelper::GetAttributeValue( $transElement, 'text' ) ) )
      {
         return $txt;
      }

      if ( isset( $transElement->text ) )
      {
         return (string) $transElement->text;
      }

      if ( ! isset( $transElement->list ) && ! isset( $transElement->dict ) )
      {
         return (string) $transElement;
      }

      return null;

   }

   private static function findList( \SimpleXMLElement $transElement ) : ?array
   {

      if ( ! isset( $transElement->list ) )
      {
         return null;
      }

      $out = [];

      if ( ! isset( $transElement->list->item ) )
      {
         return $out;
      }

      foreach ( $transElement->list->item as $itemElement )
      {
         $out[] = (string) $itemElement;
      }

      return $out;

   }

   private static function findDict( \SimpleXMLElement $transElement ) : ?array
   {

      if ( ! isset( $transElement->dict ) )
      {
         return null;
      }

      $out = [];

      if ( ! isset( $transElement->dict->item ) )
      {
         return $out;
      }

      foreach ( $transElement->dict->item as $itemElement )
      {
         $key = XmlAttributeHelper::GetAttributeValue( $itemElement, 'key' );
         if ( null === $key )
         {
            $out[] = (string) $itemElement;
         }
         else if ( \is_numeric( $key ) )
         {
            $out[ (int) $key ] = (string) $itemElement;
         }
         else
         {
            $out[ $key ] = (string) $itemElement;
         }
      }

      return $out;

   }

   // </editor-fold>


}

