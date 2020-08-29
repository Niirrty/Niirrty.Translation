<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-04-04
 * @version        0.2.0
 */


namespace Niirrty\Translation\Tests;


use Niirrty\IO\Vfs\VfsHandler;
use Niirrty\IO\Vfs\VfsManager;
use Niirrty\Locale\Locale;
use Niirrty\Translation\Sources\JSONFileSource;
use Niirrty\Translation\Tests\Fixtures\ArrayCallbackLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;


class JSONFileSourceTest extends TestCase
{


    /** @type \Niirrty\Translation\Sources\JSONFileSource */
    private $srcDe;
    /** @type \Niirrty\Translation\Sources\JSONFileSource */
    private $srcFr;
    /** @type \Niirrty\Translation\Tests\Fixtures\ArrayCallbackLogger */
    private $log;

    public function setUp()
    {

        parent::setUp();

        $this->log = new ArrayCallbackLogger();

        $this->srcDe = new JSONFileSource(
            \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations',
            new Locale( 'de', 'DE', 'utf-8' ),
            null,
            $this->log
        );

        $this->srcFr = new JSONFileSource(
            'my://data/translations',
            new Locale( 'fr', 'FR', 'utf-8' ),
            VfsManager::Create()->addHandler(
                new VfsHandler( 'MyVFS', 'my', '://', \dirname( \dirname( \dirname( __DIR__ ) ) ) ) ),
            $this->log
        );

    }

    public function testInitLogs()
    {

        $this->assertSame(
            [ LogLevel::INFO, 'Init JSON file translation source for folder "'
                                    . \dirname( \dirname( \dirname( __DIR__ ) ) )
                                    . '/data/translations".',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\JSONFileSource' ] ],
            $this->log->getMessage( 0 )
        );

    }
    public function testRead()
    {

        $this->assertSame( 'Ein Beispieltext', $this->srcDe->read( 'A example text' ) );
        $this->assertSame( 'Ein anderer Beispieltext', $this->srcDe->read( 'An other example text' ) );
        $this->assertSame( [ "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag" ], $this->srcDe->read( 'weekdays' ) );
        $this->assertSame( 'Bar', $this->srcDe->read( 'Foo', 'Bar' ) );
        $this->assertFalse( $this->srcDe->read( 'Foo' ) );

    }
    public function testReload()
    {

        $this->srcDe->setOption( 'file', null );
        $this->srcDe->setOption( 'folder', null );
        $this->srcDe->reload();
        $this->assertSame(
            [  LogLevel::NOTICE,
                'Reload data fails because there is no folder/file defined',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\AbstractFileSource' ] ],
            $this->log->lastMessage()
        );
        $this->srcFr->setOption( 'file', \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations/de_DE.json' );

    }
    public function testSetLocale()
    {

        $this->srcDe->setLocale( new Locale( 'ru', 'RU' ) );
        $this->srcDe->reload();
        $this->assertSame(
            [  LogLevel::WARNING,
                'Unable to load JSON translations file. Invalid JSON format!',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\JSONFileSource' ] ],
            $this->log->getMessage( $this->log->countMessages() - 2 )
        );

    }

}

