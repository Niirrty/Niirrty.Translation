<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-04-04
 * @version        0.1.0
 */


namespace Niirrty\Translation\Tests;


use Niirrty\IO\Vfs\VfsHandler;
use Niirrty\IO\Vfs\VfsManager;
use Niirrty\Locale\Locale;
use Niirrty\Translation\Sources\XMLFileSource;
use Niirrty\Translation\Tests\Fixtures\ArrayCallbackLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;


class XMLFileSourceTest extends TestCase
{


    /** @type \Niirrty\Translation\Sources\XMLFileSource */
    private $srcDe;
    /** @type \Niirrty\Translation\Sources\XMLFileSource */
    private $srcFr;
    /** @type \Niirrty\Translation\Tests\Fixtures\ArrayCallbackLogger */
    private $log;

    public function setUp()
    {

        parent::setUp();

        $this->log = new ArrayCallbackLogger();

        $this->srcDe = new XMLFileSource(
            \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations',
            new Locale( 'de', 'DE', 'utf-8' ),
            null,
            $this->log
        );

        $this->srcFr = new XMLFileSource(
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
            [ LogLevel::INFO, 'Init XML file translation source for folder "'
                                    . \dirname( \dirname( \dirname( __DIR__ ) ) )
                                    . '/data/translations".',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\XMLFileSource' ] ],
            $this->log->getMessage( 0 )
        );

    }
    public function testRead()
    {

        $this->assertSame( 'Ja', $this->srcDe->read( 'Yes' ) );
        $this->assertSame( 'Nein', $this->srcDe->read( 'No' ) );
        $this->assertSame( 'Vieleicht', $this->srcDe->read( 'Maybe' ) );
        $this->assertSame( [ 'Foo', 'Bar' ], $this->srcDe->read( 'MultipleValues 1' ) );
        $this->assertSame( 'Ja oder nein', $this->srcDe->read( 'Yes or no' ) );
        $this->assertSame( [ 12 => 'Foo', 14 => 'Bar' ], $this->srcDe->read( 'MultipleValues 2' ) );
        $this->assertSame( 'Bar', $this->srcDe->read( 'Foo', 'Bar' ) );

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
        $this->srcDe->setOption( 'file', \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations/de_DE.xml' );
        $this->srcDe->reload();
        $this->assertSame( 'Vieleicht', $this->srcDe->read( 'Maybe' ) );

    }
    public function testSetLocale()
    {

        $this->srcDe->setLocale( new Locale( 'de', 'DE' ) );
        $this->srcDe->reload();
        $this->assertSame( 'Vieleicht', $this->srcDe->read( 'Maybe' ) );

        $this->srcDe->setLocale( new Locale( 'it', 'IT' ) );
        $this->srcDe->reload();
        $this->assertSame(
            [  LogLevel::NOTICE,
                'Unable to get translations for locale it_IT',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\AbstractFileSource' ] ],
            $this->log->lastMessage()
        );

        $this->srcFr->setLocale( new Locale( 'ru', 'RU' ) );
        $this->srcFr->reload();
        $this->assertSame(
            [  LogLevel::WARNING,
                "Unable to load XML translations file. simplexml_load_file(): "
                . \dirname( \dirname( \dirname( __DIR__ ) ) )
                . "/data/translations/ru_RU.xml:1: parser error : Start tag expected, '",
                [ 'Class' => 'Niirrty\\Translation\\Sources\\XMLFileSource' ] ],
            $this->log->getMessage( $this->log->countMessages() - 2 )
        );
        $this->srcFr->setLocale( new Locale( 'de', 'CH' ) );
        $this->srcFr->reload();
        $this->assertSame(
            [  LogLevel::NOTICE,
                'Unable to get translations for locale de_CH',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\AbstractFileSource' ] ],
            $this->log->getMessage( $this->log->countMessages() - 1 )
        );
        $this->srcFr->setLocale( new Locale( 'en', 'GB' ) );
        $this->srcFr->setTranslationsFolder( \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations' );
        $this->srcFr->reload();
        $this->assertSame(
            [  LogLevel::NOTICE,
                'Parse-Error: Invalid XML translation file format',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\XMLFileSource' ] ],
            $this->log->getMessage( $this->log->countMessages() - 2 )
        );
        $this->srcFr->setLocale( new Locale( 'de', 'LU' ) );
        $this->srcFr->reload();
        $this->assertSame(
            [  LogLevel::NOTICE,
                'Parse-Error: Invalid trans element at index 0. Missing a Identifier-Definition.',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\XMLFileSource' ] ],
            $this->log->getMessage( $this->log->countMessages() - 2 )
        );
        $this->srcFr->setLocale( new Locale( 'de', 'LI' ) );
        $this->srcFr->reload();
        $this->assertSame(
            [  LogLevel::NOTICE,
                'Parse-Error: Invalid trans element at index 0. Missing a Text/List/Dict.',
                [ 'Class' => 'Niirrty\\Translation\\Sources\\XMLFileSource' ] ],
            $this->log->getMessage( $this->log->countMessages() - 2 )
        );
        $this->srcFr->setLocale( new Locale( 'de', 'NL' ) );
        $this->srcFr->reload();
        $this->assertSame( [], $this->srcFr->read( 'MultipleValues 1' ) );

    }
    public function testSetData()
    {

        $this->srcDe->setData( [ 'foo' => 'Foo' ], true );

        $this->assertSame( 'Foo', $this->srcDe->read( 'foo' ) );

    }
    public function testGetTranslationsFolder()
    {

        $this->assertSame( \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations', $this->srcDe->getTranslationsFolder() );

    }
    public function testGetSetFileExtension()
    {

        $this->assertSame( 'foo', $this->srcDe->setFileExtension( 'foo' )->getFileExtension() );

    }
    public function testSetVfsManager()
    {

        $this->assertNull( $this->srcFr->setVfsManager( null ) ->getVfsManager() );

    }
    public function testGetLogger()
    {

        $this->assertSame( $this->log, $this->srcDe->getLogger() );

    }
    #public function test() { $this->assertSame( '', '' ); }

}
