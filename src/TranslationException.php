<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Niirrty
 * @package        Niirrty\Translation
 * @since          2017-11-01
 * @version        0.3.0
 */


declare( strict_types = 1 );


namespace Niirrty\Translation;


use Niirrty\NiirrtyException;


/**
 * Defines a class that …
 *
 * @since v0.1.0
 */
class TranslationException extends NiirrtyException
{


    // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

    /**
     * TranslationException constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param null|\Throwable $previous
     */
    public function __construct( string $message, int $code = 0, ?\Throwable $previous = null )
    {

        parent::__construct( $message, $code, $previous );

    }

    // </editor-fold>


}

