<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2018-2020, Niirrty
 * @license        MIT
 * @since          2018-04-05
 * @version        0.3.1
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use Niirrty\IO\Vfs\IVfsManager;
use Niirrty\Locale\Locale;
use Psr\Log\LoggerInterface;


abstract class AbstractFileSource extends AbstractSource
{


    #region // –––––––   C O N S T R U C T O R   A N D / O R   D E S T R U C T O R   ––––––––

    /**
     * AbstractFileSource constructor.
     *
     * @param string                $transFolder The folder/directory where the translation files are located
     * @param string                $fileExtension
     * @param Locale                $locale      The locale of the required translations
     * @param LoggerInterface|null  $logger      An optional logger.
     * @param IVfsManager|null      $vfsManager  Optional Virtual File System Manager if $transFolder requires
     */
    protected function __construct(
        string $transFolder, string $fileExtension, Locale $locale, ?LoggerInterface $logger = null,
        ?IVfsManager $vfsManager = null )
    {

        parent::__construct( $locale, $logger );

        $this->_options[ 'vfsManager'    ] = $vfsManager;
        $this->_options[ 'folder'        ] = $transFolder;
        $this->_options[ 'fileExtension' ] = $fileExtension;

    }

    #endregion


    #region // –––––––   P U B L I C   M E T H O D S   ––––––––––––––––––––––––––––––––––––––

    /**
     * Gets the optional virtual file system manager, used for the file source
     *
     * @return IVfsManager|null
     */
    public final function getVfsManager() : ?IVfsManager
    {

        return $this->_options[ 'vfsManager' ];

    }

    /**
     * Gets the folder/dir where the translation files are located.
     *
     * @return null|string
     */
    public final function getTranslationsFolder() : ?string
    {

        return $this->_options[ 'folder' ];

    }

    /**
     * Gets the file name extension of the usable translation files.
     *
     * @return string
     */
    public final function getFileExtension() : string
    {

        return $this->_options[ 'fileExtension' ];

    }

    /**
     * Sets the optional virtual file system manager, used for the file source
     *
     * @param IVfsManager|null $manager
     * @return AbstractFileSource
     */
    public final function setVfsManager( ?IVfsManager $manager )
    {

        $this->_options[ 'vfsManager' ] = $manager;

        unset ( $this->_options[ 'data' ] );

        return $this;

    }

    /**
     * Sets the folder/dir where the translation files are located.
     *
     * @param  string|null $folder
     * @return AbstractFileSource
     */
    public final function setTranslationsFolder( ?string $folder )
    {

        $this->_options[ 'folder' ] = $folder;

        unset ( $this->_options[ 'data' ] );

        return $this;

    }

    /**
     * Sets the file name extension of the usable translation files.
     *
     * @param  string $extension
     * @return AbstractFileSource
     */
    public final function setFileExtension( string $extension )
    {

        $this->_options[ 'fileExtension' ] = $extension;

        unset ( $this->_options[ 'data' ] );

        return $this;

    }

    /**
     * Sets a new array with translation data that should be used.
     *
     * The array keys are the identifiers (string|int) the values must be arrays with items 'text' and optionally
     * with 'category' or the values is a string that will be converted to [ 'text' => $value ]
     *
     * @param array $data
     * @param bool  $doReload
     * @return AbstractFileSource
     */
    public function setData( array $data, bool $doReload = true )
    {

        $this->logInfo( 'Manual set new data' . ( $doReload ? ' and reload.' : '.' ), __CLASS__ );

        $this->_options[ 'data' ] = $data;

        if ( $doReload )
        {
            $this->reload();
        }

        return $this;

    }

    private function hasVfsManager() : bool
    {

        return null !== $this->_options[ 'vfsManager' ];

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

        if ( ! isset( $this->_options[ 'data' ] ) )
        {
            $this->reload();
        }

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
     * @return AbstractFileSource
     */
    public function reload()
    {

        if ( isset( $this->_options[ 'folder' ] ) )
        {
            return $this->reloadFromFolder();
        }

        if ( ! isset( $this->_options[ 'file' ] ) || ! \file_exists( $this->_options[ 'file' ] ) )
        {
            $this->logNotice( 'Reload data fails because there is no folder/file defined', __CLASS__ );
            return $this;
        }

        return $this->reloadFromFile();

    }

    #endregion


    #region // -------   P R O T E C T E D   M E T H O D S   --------------------------------

    /**
     * @return AbstractFileSource
     */
    protected function reloadFromFolder()
    {

        $languageFolderBase = $this->_options[ 'folder' ];

        $this->logInfo( 'Reload data from folder "' . $languageFolderBase . '".', __CLASS__ );

        if ( $this->hasVfsManager() )
        {
            $languageFolderBase = $this->getVfsManager()->parsePath( $languageFolderBase );
        }

        $languageFolderBase = \rtrim( $languageFolderBase, '\\/' );

        if ( ! empty( $languageFolderBase ) ) { $languageFolderBase .= '/'; }

        $locale = $this->getLocale();

        $languageFile = $languageFolderBase . $locale->getLID() . '_' . $locale->getCID();

        if ( \strlen( $locale->getCharset() ) > 0 )
        {
            $languageFile .= '.' . $locale->getCharset() . '.' . $this->_options[ 'fileExtension' ];
        }
        else
        {
            $languageFile .= '.' . $this->_options[ 'fileExtension' ];
        }

        if ( ! \file_exists( $languageFile ) )
        {
            $languageFile = $languageFolderBase . $locale->getLID() . '_' . $locale->getCID()
                              . '.' . $this->_options[ 'fileExtension' ];
        }

        if ( ! \file_exists( $languageFile ) )
        {
            $languageFile = $languageFolderBase . $locale->getLID() . '.' . $this->_options[ 'fileExtension' ];
        }

        if ( ! \file_exists( $languageFile ) )
        {
            unset(
                $this->_options[ 'file' ],
                $this->_options[ 'folder' ]
            );
            $this->logNotice( 'Unable to get translations for locale ' . $locale, __CLASS__ );
            return $this;
        }

        $this->_options[ 'file' ]   = $languageFile;
        $this->_options[ 'folder' ] = $languageFolderBase;

        return $this->reloadFromFile();

    }

    /**
     * @return AbstractFileSource
     */
    protected abstract function reloadFromFile();

    #endregion


}

