<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-04-03
 * @version        0.2.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use Niirrty\IO\Vfs\IVfsManager;
use Niirrty\Locale\Locale;
use Niirrty\XmlAttributeHelper;
use Psr\Log\LoggerInterface;


class XMLFileSource extends AbstractFileSource
{


   // <editor-fold desc="// –––––––   C O N S T R U C T O R   A N D / O R   D E S T R U C T O R   ––––––––">

   /**
    * XMLFileSource constructor.
    *
    * @param string                            $folder
    * @param \Niirrty\Locale\Locale            $locale
    * @param \Niirrty\IO\Vfs\IVfsManager|null  $vfsManager
    * @param null|\Psr\Log\LoggerInterface     $logger
    */
   public function __construct(
      string $folder, Locale $locale, ?IVfsManager $vfsManager = null, ?LoggerInterface $logger = null )
   {

      parent::__construct( $folder, 'xml', $locale, $logger, $vfsManager );

      $this->logInfo( 'Init XML file translation source for folder "' . $folder . '".', __CLASS__ );

   }

   // </editor-fold>


   // <editor-fold desc="// –––––––   P U B L I C   M E T H O D S   ––––––––––––––––––––––––––––––––––––––">

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

   // </editor-fold>


   // <editor-fold desc="// –––––––   P R I V A T E   M E T H O D S   ––––––––––––––––––––––––––––––––––––">

   /**
    * @return \Niirrty\Translation\Sources\XMLFileSource
    */
   protected function reloadFromFile()
   {

      $this->logInfo( 'Load data from XML file "' . $this->_options[ 'file' ] . '".', __CLASS__ );

      $translations = [];

      try
      {
         $xmlDoc = \simplexml_load_file( $this->_options[ 'file' ] );
         $translations = $this->parseXML( $xmlDoc );
      }
      catch ( \Throwable $ex )
      {
         $this->logWarning( 'Unable to load XML translations file. ' . $ex->getMessage(), __CLASS__ );
         $translations = [];
      }

      if ( ! isset( $this->_options[ 'data' ] ) )
      {
         $this->_options[ 'data' ] = [];
      }

      $this->setData( \array_merge( $this->_options[ 'data' ], $translations ), false );

      return $this;

   }

   private function parseXML( \SimpleXMLElement $xmlDoc ) : array
   {

      $out = [];

      if ( ! isset( $xmlDoc->trans ) )
      {
         $this->logNotice( 'Parse-Error: Invalid XML translation file format', __CLASS__ );
         return $out;
      }

      $elementIndex = 0;
      foreach ( $xmlDoc->trans as $transElement )
      {
         if ( null === ( $id = $this->findId( $transElement ) ) )
         {
            $this->logNotice(
               'Parse-Error: Invalid trans element at index ' . $elementIndex . '. Missing a Identifier-Definition.',
               __CLASS__ );
            continue;
         }
         if ( null === ( $txt = $this->findText( $transElement ) ) )
         {
            if ( null === ( $txt = $this->findList( $transElement ) ) )
            {
               if ( null === ( $txt = $this->findDict( $transElement ) ) )
               {
                  $this->logNotice(
                     'Parse-Error: Invalid trans element at index ' . $elementIndex . '. Missing a Text/List/Dict.',
                     __CLASS__ );
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
         $str = (string) $transElement;
         if ( '' === $str )
         {
            return null;
         }
         return $str;
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

