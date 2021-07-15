<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Niirrty
 * @package        Niirrty\Translation\Sources
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use \Niirrty\IO\Vfs\IVfsManager;
use \Niirrty\Locale\Locale;
use \Psr\Log\LoggerInterface;


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
class PHPFileSource extends AbstractFileSource
{


    #region // – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –

    /**
     * PHPFileSource constructor.
     *
     * @param string                           $folder
     * @param Locale $locale
     * @param IVfsManager|null $vfsManager
     * @param null|LoggerInterface $logger
     */
    public function __construct(
        string $folder, Locale $locale, ?IVfsManager $vfsManager = null, ?LoggerInterface $logger = null )
    {

        parent::__construct( $folder, 'php', $locale, $logger, $vfsManager );

        $this->logInfo( 'Init PHP file translation source for folder "' . $folder . '".', __CLASS__ );

    }

    #endregion


    #region //// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –

    /**
     * Sets a options value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return PHPFileSource
     */
    public function setOption( string $name, mixed $value ) : PHPFileSource
    {

        parent::setOption( $name, $value );

        return $this;

    }

    #endregion


    #region //// – – –   P R I V A T E   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – –

    /**
     * @return PHPFileSource
     */
    protected function reloadFromFile() : PHPFileSource
    {

        $this->logInfo( 'Load data from file "' . $this->_options[ 'file' ] . '".', __CLASS__ );

        try
        {
            /** @noinspection PhpIncludeInspection */
            $translations = include $this->_options[ 'file' ];
        }
        catch ( \Throwable $ex )
        {
            $this->logNotice( 'Unable to include translations file.' . $ex->getMessage(), __CLASS__ );
            $translations = [];
        }

        if ( ! \is_array( $translations ) )
        {
            $this->logNotice( 'Invalid translations file format.', __CLASS__ );
            $translations = [];
        }

        if ( ! isset( $this->_options[ 'data' ] ) )
        {
            $this->_options[ 'data' ] = [];
        }

        $this->setData( \array_merge( $this->_options[ 'data' ], $translations ), false );

        return $this;

    }

    #endregion


}

