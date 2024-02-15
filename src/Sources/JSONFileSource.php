<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-04-03
 * @version        0.4.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use \Niirrty\IO\Vfs\IVfsManager;
use \Niirrty\Locale\Locale;
use \Psr\Log\LoggerInterface;


class JSONFileSource extends AbstractFileSource
{


    #region // –––––––   C O N S T R U C T O R   A N D / O R   D E S T R U C T O R   ––––––––

    /**
     * JSONFileSource constructor.
     *
     * @param string                           $folder
     * @param Locale $locale
     * @param IVfsManager|null $vfsManager
     * @param null|LoggerInterface $logger
     */
    public function __construct(
        string $folder, Locale $locale, ?IVfsManager $vfsManager = null, ?LoggerInterface $logger = null )
    {

        parent::__construct( $folder, 'json', $locale, $logger, $vfsManager );

        $this->logInfo( 'Init JSON file translation source for folder "' . $folder . '".', __CLASS__ );

    }

    #endregion


    #region // –––––––   P U B L I C   M E T H O D S   ––––––––––––––––––––––––––––––––––––––

    /**
     * Sets a options value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return JSONFileSource
     */
    public function setOption( string $name, mixed $value ) : self
    {

        return parent::setOption( $name, $value );

    }

    #endregion


    #region // –––––––   P R I V A T E   M E T H O D S   ––––––––––––––––––––––––––––––––––––

    /**
     * @return JSONFileSource
     */
    protected function reloadFromFile() : self
    {

        $this->logInfo( 'Reload data from file "' . $this->_options[ 'file' ] . '".', __CLASS__ );

        try
        {
            $translations = \json_decode( \file_get_contents( $this->_options[ 'file' ] ), true );
        }
        catch ( \Throwable $ex )
        {
            $this->logWarning( 'Unable to load JSON translations file. ' . $ex->getMessage(), __CLASS__ );
            $translations = [];
        }

        if ( ! \is_array( $translations ) )
        {
            $this->logWarning( 'Unable to load JSON translations file. Invalid JSON format!', __CLASS__ );
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

