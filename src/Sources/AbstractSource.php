<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Niirrty
 * @package        Niirrty\Translation\Sources
 * @since          2017-11-01
 * @version        0.3.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation\Sources;


use Niirrty\Locale\Locale;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


/**
 * Defines a abstract ISource implementation.
 *
 * @since v0.1.0
 */
abstract class AbstractSource implements ISource
{


    // <editor-fold desc="// – – –   P R O T E C T E D   F I E L D S   – – – – – – – – – – – – – – – – – – – – – –">

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
     *
     * @param Locale $locale
     * @param null|LoggerInterface $logger Optional logger
     */
    protected function __construct( Locale $locale, ?LoggerInterface $logger = null )
    {

        $this->_options[ 'locale' ] = $locale;

        $this->setLogger( $logger );

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

    /**
     * Gets the current defined locale.
     *
     * @return Locale
     */
    public final function getLocale() : Locale
    {

        return $this->_options[ 'locale' ];

    }

    /**
     * Gets the current defined logger.
     *
     * @return LoggerInterface
     */
    public final function getLogger() : LoggerInterface
    {

        return $this->_options[ 'logger' ];

    }

    /**
     * Sets a new locale.
     *
     * @param Locale $locale
     * @return ISource
     */
    public final function setLocale( Locale $locale )
    {

        $this->_options[ 'locale' ] = $locale;

        unset( $this->_options[ 'data' ] );

        return $this;

    }

    /**
     * Sets a new logger or null if no logger should be used.
     *
     * @param LoggerInterface|null $logger
     * @return ISource
     */
    public final function setLogger( ?LoggerInterface $logger )
    {

        $this->_options[ 'logger' ] = null === $logger ? new NullLogger() : $logger;

        unset( $this->_options[ 'data' ] );

        return $this;

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
     * Gets the option value of option with defined name or $defaultValue if the option is unknown.
     *
     * @param string $name The name of the option.
     * @param mixed  $defaultValue This value is returned if the option not exists.
     * @return mixed
     */
    public final function getOption( string $name, $defaultValue = false )
    {

        if ( ! $this->hasOption( $name ) )
        {
            return $defaultValue;
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
     * @return AbstractSource
     */
    public function setOption( string $name, $value )
    {

        $this->_options[ $name ] = $value;

        unset( $this->_options[ 'data' ] );

        return $this;

    }

    // </editor-fold>


    // <editor-fold desc="// –––––––   P R O T E C T E D   M E T H O D S   ––––––––––––––––––––––––––––––––">

    protected function logInfo( string $message, string $class )
    {

        $this->_options[ 'logger' ]->info( $message, [ 'Class' => $class ] );

    }
    protected function logNotice( string $message, string $class )
    {

        $this->_options[ 'logger' ]->notice( $message, [ 'Class' => $class ] );

    }
    protected function logWarning( string $message, string $class )
    {

        $this->_options[ 'logger' ]->warning( $message, [ 'Class' => $class ] );

    }

    // </editor-fold>


}

