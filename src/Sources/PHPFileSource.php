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


use \Niirrty\IO\Vfs\Manager;
use Niirrty\Locale\Locale;
use Psr\Log\LoggerInterface;


/**
 * A php file source declares all translations of a specific locale inside an PHP file array
 *
 * Loads a translation array source from a specific folder that contains one or more locale depending PHP files.
 *
 * E.G: if the defined $folder is '/var/www/example.com/translations' and the declared Locale is de_DE.UTF-8
 *
 * it tries to use:
 *
 * - /var/www/example.com/translations/de_DE.UTF-8.php
 * - /var/www/example.com/translations/de_DE.php
 * - /var/www/example.com/translations/de.php
 *
 * The used file should be declared like for translations with numeric indicators
 *
 * <code>
 * return [
 *
 *    1 => 'Übersetzter Text',
 *    2 => 'Anderer übersetzter Text',
 *    4 => [ 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag' ]
 *
 * ];
 * </code>
 *
 * or for translations with string indicators:
 *
 * <code>
 * return [
 *
 *    'Translated text' => 'Übersetzter Text',
 *    'Other translated text 1' => 'Anderer übersetzter Text',
 *    'WeekDays' => [ 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag' ]
 *
 * ];
 * </code>
 */
class PHPFileSource extends AbstractSource
{


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * PHPFileSource constructor.
    *
    * Init a new PHPFileSource instance.
    *
    * @param string                        $folder
    * @param \Niirrty\Locale\Locale        $locale
    * @param \Niirrty\IO\Vfs\Manager|null  $vfsManager The optional virtual file system manager
    * @param null|\Psr\Log\LoggerInterface $logger
    */
   public function __construct(
      string $folder, Locale $locale, ?Manager $vfsManager = null, ?LoggerInterface $logger = null )
   {

      parent::__construct( $locale, $logger );

      $this->_options[ 'folder'     ] = $folder;
      $this->_options[ 'vfsManager' ] = $vfsManager;
      $this->_log->info( 'Init PHP file translation source for folder "' . $folder . '".', [ 'Class' => __CLASS__ ] );
      $this->reload();

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Sets a new array with translation data that should be used.
    *
    * The array keys are the identifiers (string|int) the values must be arrays with items 'text' and optionally
    * with 'category' or the values is a string that will be converted to [ 'text' => $value ]
    *
    * @param array $data
    * @param bool  $doReload
    * @return \Niirrty\Translation\Sources\PHPFileSource
    */
   public function setData( array $data, bool $doReload = true )
   {

      $this->_log->info( 'Manual set new data' . ( $doReload ? ' and reload.' : '.' ), [ 'Class' => __CLASS__ ] );

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
    * @return \Niirrty\Translation\Sources\PHPFileSource
    */
   public function reload()
   {

      if ( isset( $this->_options[ 'folder' ] ) )
      {
         return $this->reloadFromFolder();
      }

      if ( ! isset( $this->_options[ 'file' ] ) || ! \file_exists( $this->_options[ 'file' ] ) )
      {
         $this->_log->notice( 'Reload data fails because there is no folder/file defined', [ 'Class' => __CLASS__ ] );
         $this->_isValid = false;
         return $this;
      }

      return $this->reloadFromFile();

   }

   /**
    * Sets a options value.
    *
    * @param string $name
    * @param mixed  $value
    * @return \Niirrty\Translation\Sources\PHPFileSource
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


   // <editor-fold desc="// – – –   P R I V A T E   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * @return \Niirrty\Translation\Sources\PHPFileSource
    */
   private function reloadFromFolder()
   {

      $this->_isValid = false;

      $languageFolderBase = $this->_options[ 'folder' ];

      $this->_log->info( 'Reload data from folder "' . $languageFolderBase . '".', [ 'Class' => __CLASS__ ] );

      if ( $this->hasVfsManager() )
      {
         $languageFolderBase = $this->getVfsManager()->parsePath( $languageFolderBase );
      }

      $languageFolderBase = \rtrim( $languageFolderBase, '\\/' );

      if ( ! empty( $languageFolderBase ) ) { $languageFolderBase .= '/'; }

      $languageFile = $languageFolderBase . $this->_locale->getLID() . '_' . $this->_locale->getCID();

      if ( \strlen( $this->_locale->getCharset() ) > 0 )
      {
         $languageFile .= '/' . $this->_locale->getCharset() . '.php';
      }
      else
      {
         $languageFile .= '.php';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         $languageFile = $languageFolderBase . $this->_locale->getLID() . '_' . $this->_locale->getCID() . '.php';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         $languageFile = $languageFolderBase . $this->_locale->getLID() . '.php';
      }

      if ( ! \file_exists( $languageFile ) )
      {
         unset(
            $this->_options[ 'file' ],
            $this->_options[ 'folder' ]
         );
         $this->_log->notice( 'Unable to get translations for locale ' . $this->_locale, [ 'Class' => __CLASS__ ] );
         $this->_isValid = false;
         return $this;
      }

      $this->_options[ 'file' ]   = $languageFile;
      $this->_options[ 'folder' ] = $languageFolderBase;

      return $this->reloadFromFile();

   }

   /**
    * @return \Niirrty\Translation\Sources\PHPFileSource
    */
   private function reloadFromFile()
   {

      $this->_isValid = false;

      $this->_log->info( 'Reload data from file "' . $this->_options[ 'file' ] . '".', [ 'Class' => __CLASS__ ] );

      try
      {
         /** @noinspection PhpIncludeInspection */
         $translations = include $this->_options[ 'file' ];
      }
      catch ( \Throwable $ex )
      {
         $this->_log->notice( 'Unable to include translations file.' . $ex->getMessage(), [ 'Class' => __CLASS__ ] );
         $translations = [];
         $this->_isValid = false;
      }

      if ( ! \is_array( $translations ) )
      {
         $this->_log->notice( 'Invalid translations file format.', [ 'Class' => __CLASS__ ] );
         $translations = [];
         $this->_isValid = false;
      }

      if ( ! isset( $this->_options[ 'data' ] ) )
      {
         $this->_options[ 'data' ] = [];
      }

      $this->_isValid = true;

      return $this->setData( \array_merge( $this->_options[ 'data' ], $translations ), false );

   }

   // </editor-fold>


}

