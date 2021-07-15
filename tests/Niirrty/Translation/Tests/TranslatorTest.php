<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-04-06
 * @version        0.1.0
 */


namespace Niirrty\Translation\Tests;


use Niirrty\Locale\Locale;
use Niirrty\Translation\Sources\ISource;
use Niirrty\Translation\Sources\PHPFileSource;
use Niirrty\Translation\Tests\Fixtures\ArrayCallbackLogger;
use Niirrty\Translation\TranslationException;
use Niirrty\Translation\Translator;
use PHPUnit\Framework\TestCase;


class TranslatorTest extends TestCase
{


    /** @type \Psr\Log\LoggerInterface */
    private $log;
    /** @type \Niirrty\Locale\Locale */
    private $lcDeDE;
    /** @type \Niirrty\Locale\Locale */
    private $lcDeAT;
    /** @type \Niirrty\Locale\Locale */
    private $lcFrFR;
    /** @type \Niirrty\Translation\Sources\ISource */
    private $src;
    /** @type \Niirrty\Translation\Translator */
    private $trans;

    public function setUp() : void
    {

        parent::setUp();

        $this->log = new ArrayCallbackLogger();
        $this->lcDeDE = new Locale( 'de', 'DE', 'utf-a' );
        $this->lcDeAT = new Locale( 'de', 'AT' );
        $this->lcFrFR = new Locale( 'fr', 'FR', 'utf-8' );
        $this->src = new PHPFileSource(
            \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations', $this->lcFrFR );
        $this->trans = new Translator( $this->lcDeDE );
        Translator::RemoveInstance();

    }

    public function testConstruct()
    {

        $this->assertInstanceOf( Translator::class, $this->trans );

    }
    public function testGetSource()
    {

        $this->assertNull( $this->trans->getSource( 'foo' ) );
        $this->trans->addSource( 'foo', $this->src );
        $this->assertInstanceOf( ISource::class, $this->trans->getSource( 'foo' ) );
        $this->lcDeDE->registerAsGlobalInstance();
        $trans = new Translator();

    }
    public function testRemoveSource()
    {

        $this->assertNull( $this->trans->getSource( 'foo' ) );
        $this->trans->addSource( 'foo', $this->src );
        $this->assertInstanceOf( ISource::class, $this->trans->getSource( 'foo' ) );
        $this->trans->removeSource( 'foo' );
        $this->assertNull( $this->trans->getSource( 'foo' ) );

    }
    public function testCleanSources()
    {

        $this->assertSame( 0, $this->trans->countSources() );
        $this->trans->addSource( 'foo', $this->src );
        $this->trans->addSource( 'bar', $this->src );
        $this->assertSame( 2, $this->trans->countSources() );
        $this->trans->cleanSources();
        $this->assertFalse($this->trans->hasSources() );

    }
    public function testRead()
    {

        $this->trans->addSource( '_', $this->src );

        $this->assertSame( 'Ein Beispieltext', $this->trans->read( 'A example text' ) );
        $this->assertSame( 'Ein anderer Beispieltext', $this->trans->read( 'An other example text', '_' ) );
        $this->assertSame( '…', $this->trans->read( 'A other example text', '_', '…' ) );
        $this->assertSame( '…', $this->trans->read( 'A other example text', null, '…' ) );

    }
    public function testGetSources()
    {

        $this->assertSame( [], $this->trans->getSources() );
        $this->trans->addSource( '_', $this->src );
        $this->assertSame( [ '_' => $this->src ], $this->trans->getSources() );

    }
    public function testGetSourcesIterator()
    {

        $this->assertFalse( $this->trans->getSourcesIterator()->valid() );
        $this->trans->addSource( '_', $this->src );
        $this->assertTrue( $this->trans->getSourcesIterator()->valid() );

    }
    public function testHasSource()
    {

        $this->assertFalse( $this->trans->hasSource( '_' ) );
        $this->trans->addSource( '_', $this->src );
        $this->assertTrue( $this->trans->hasSource( '_' ) );

    }
    public function testGetSourceNames()
    {

        $this->assertSame( [], $this->trans->getSourceNames() );
        $this->trans->addSource( '_', $this->src );
        $this->trans->addSource( '-', $this->src );
        $this->assertSame( [ '_', '-' ], $this->trans->getSourceNames() );

    }
    public function testSetAsGlobalInstance()
    {

        $this->assertFalse( Translator::HasInstance() );
        $this->trans->setAsGlobalInstance();
        $this->assertTrue( Translator::HasInstance() );
        $this->assertSame( $this->trans, Translator::GetInstance() );
        Translator::RemoveInstance();
        $this->assertInstanceOf( Translator::class, Translator::GetInstance() );

    }

}
