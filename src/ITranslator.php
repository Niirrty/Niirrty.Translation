<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2018-2021, Niirrty
 * @license        MIT
 * @since          2018-04-06
 * @version        0.4.0
 */


namespace Niirrty\Translation;


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
interface ITranslator
{

    /**
     * Gets the source with defined name or NULL.
     *
     * @param string $sourceName
     * @return ISource|null
     */
    public function getSource( string $sourceName ) : ?ISource;

    /**
     * Adds a source with an associated name.
     *
     * @param string                               $sourceName The unique source name
     * @param ISource $source
     * @return ITranslator
     */
    public function addSource( string $sourceName, ISource $source ): ITranslator;

    /**
     * Removes a source
     *
     * @param string $sourceName The source name
     * @return ITranslator
     */
    public function removeSource( string $sourceName ): ITranslator;

    /**
     * Removes all sources.
     *
     * @return ITranslator
     */
    public function cleanSources(): ITranslator;

    /**
     * Reads the translation and return it.
     *
     * The returned translation can be of each known type, depending to the requirements.
     *
     * If a valid source index is defined, only this source is used.
     *
     * @param int|string  $identifier         The translation identifier
     * @param string|null $sourceName         The name of the source or NULL for search all sources
     * @param mixed  $defaultTranslation Is returned if the translation was not found
     * @return mixed
     */
    public function read( int|string $identifier, ?string $sourceName = null, mixed $defaultTranslation = false ): mixed;

    /**
     * Gets a array with all defined sources. THe keys are the associated source names.
     *
     * @return array
     */
    public function getSources() : array;

    /**
     * Gets a iterator over all defined sources.
     *
     * @return \Generator
     */
    public function getSourcesIterator() : \Generator;

    /**
     * Gets if one or more sources are defined.
     *
     * @return bool
     */
    public function hasSources() : bool;

    /**
     * Gets if a source with defined name exists.
     *
     * @param string $sourceName
     * @return bool
     */
    public function hasSource( string $sourceName ) : bool;

    /**
     * Return how many sources currently are defined.
     *
     * @return int
     */
    public function countSources() : int;

    /**
     * Gets the names of all defined sources.
     *
     * @return array
     */
    public function getSourceNames() : array;

}
