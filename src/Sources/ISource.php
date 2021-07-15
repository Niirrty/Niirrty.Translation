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


use \Niirrty\Locale\Locale;
use \Psr\Log\LoggerInterface;


/**
 * Each translation source must implement this interface.
 *
 * @since v0.1.0
 */
interface ISource
{

    /**
     * Reads one or more translation values.
     *
     * @param int|string|null $identifier
     * @param mixed $defaultTranslation Is returned if no translation was found for defined identifier.
     *
     * @return mixed
     */
    public function read( int|string|null $identifier, mixed $defaultTranslation = false ): mixed;

    /**
     * Gets the current defined locale.
     *
     * @return Locale
     */
    public function getLocale() : Locale;

    /**
     * Gets the current defined logger.
     *
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface;

    /**
     * Gets all options of the translation source.
     *
     * @return array
     */
    public function getOptions() : array;

    /**
     * Gets the option value of option with defined name or FALSE if the option is unknown.
     *
     * @param string     $name         The name of the option.
     * @param mixed $defaultValue This value is remembered and returned if the option not exists
     *
     * @return mixed
     */
    public function getOption( string $name, mixed $defaultValue = false ): mixed;

    /**
     * Sets a new locale.
     *
     * @param Locale $locale
     * @return ISource
     */
    public function setLocale( Locale $locale ): ISource;

    /**
     * Sets a new logger or null if no logger should be used.
     *
     * @param LoggerInterface|null $logger
     * @return ISource
     */
    public function setLogger( ?LoggerInterface $logger ): ISource;

    /**
     * Sets a options value.
     *
     * @param string $name
     * @param mixed $value
     * @return ISource
     */
    public function setOption( string $name, mixed $value ): ISource;

    /**
     * Gets if an option with defined name exists.
     *
     * @param string $name The option name.
     * @return bool
     */
    public function hasOption( string $name ) : bool;

    /**
     * Reload the source by current defined options.
     *
     * @return ISource
     */
    public function reload(): ISource;


}

