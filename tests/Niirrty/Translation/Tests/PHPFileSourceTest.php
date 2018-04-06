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
use Niirrty\Translation\Sources\PHPFileSource;
use Niirrty\Translation\Tests\Fixtures\ArrayCallbackLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;


class PHPFileSourceTest extends TestCase
{


   /** @type \Niirrty\Translation\Sources\PHPFileSource */
   private $srcDe;
   /** @type \Niirrty\Translation\Sources\PHPFileSource */
   private $srcFr;
   /** @type \Niirrty\Translation\Tests\Fixtures\ArrayCallbackLogger */
   private $log;

   public function setUp()
   {

      parent::setUp();

      $this->log = new ArrayCallbackLogger();

      $this->srcDe = new PHPFileSource(
         \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations',
         new Locale( 'de', 'DE', 'utf-8' ),
         null,
         $this->log
      );

      $this->srcFr = new PHPFileSource(
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
         [ LogLevel::INFO, 'Init PHP file translation source for folder "'
                           . \dirname( \dirname( \dirname( __DIR__ ) ) )
                           . '/data/translations".', [ 'Class' => 'Niirrty\\Translation\\Sources\\PHPFileSource' ] ],
         $this->log->getMessage( 0 )
      );
      $this->assertSame(
         [  LogLevel::INFO,
            'Init PHP file translation source for folder "my://data/translations".',
            [ 'Class' => 'Niirrty\\Translation\\Sources\\PHPFileSource' ] ],
         $this->log->getMessage( 1 )
      );

   }
   public function testRead()
   {

      $this->assertSame( 'Ein Beispieltext', $this->srcDe->read( 'A example text' ) );
      $this->assertSame( 'Bar', $this->srcDe->read( 'Foo', 'Bar' ) );
      $this->assertSame( 'Un exemple de texte', $this->srcFr->read( 'A example text' ) );
      $this->assertSame( [
                            'A example text' => 'Ein Beispieltext',
                            'An other example text' => 'Ein anderer Beispieltext',
                            'weekdays' => [ 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag' ]
                         ], $this->srcDe->read( null ) );

   }
   public function testReload()
   {

      $this->srcDe->setTranslationsFolder( null );
      $this->srcDe->setOption( 'file', null );
      $this->srcDe->reload();
      $this->assertSame(
         [  LogLevel::NOTICE,
            'Reload data fails because there is no folder/file defined',
            [ 'Class' => 'Niirrty\\Translation\\Sources\\AbstractFileSource' ] ],
         $this->log->lastMessage()
      );
      $this->srcDe->setTranslationsFolder( \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations' );
      $this->srcDe->reload();
      $this->assertSame( 'Ein anderer Beispieltext', $this->srcDe->read( 'An other example text' ) );

   }
   public function testSetLocale()
   {

      $this->srcDe->setLocale( new Locale( 'de', 'DE' ) );
      $this->assertSame( 'Ein anderer Beispieltext', $this->srcDe->read( 'An other example text' ) );

      $this->srcDe->setLocale( new Locale( 'it', 'IT' ) );
      $this->srcDe->reload();
      $this->assertSame(
         [  LogLevel::NOTICE,
            'Unable to get translations for locale it_IT',
            [ 'Class' => 'Niirrty\\Translation\\Sources\\AbstractFileSource' ] ],
         $this->log->lastMessage()
      );

      $this->srcDe->setOption( 'folder', \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations' );
      $this->srcDe->setLocale( new Locale( 'ru', 'RU' ) );
      $this->srcDe->reload();
      $this->assertSame(
         [  LogLevel::NOTICE,
            'Unable to include translations file.fopen(/foo/bar/baz19293949596979899909): failed to open stream: No such file or directory',
            [ 'Class' => 'Niirrty\\Translation\\Sources\\PHPFileSource' ] ],
         $this->log->getMessage( $this->log->countMessages() - 2 )
      );
      $this->srcDe->setLocale( new Locale( 'de', 'CH' ) );
      $this->srcDe->reload();
      $this->assertSame(
         [  LogLevel::NOTICE,
            'Invalid translations file format.',
            [ 'Class' => 'Niirrty\\Translation\\Sources\\PHPFileSource' ] ],
         $this->log->getMessage( $this->log->countMessages() - 2 )
      );

   }
   public function testSetData()
   {

      $this->srcDe->setData( [ 'foo' => 'Foo' ], true );

      $this->assertSame( 'Foo', $this->srcDe->read( 'foo' ) );

   }
   public function testGetLocale()
   {

      $this->assertSame( 'de_DE.utf-8', (string) $this->srcDe->getLocale() );
      $this->assertSame( 'fr_FR.utf-8', (string) $this->srcFr->getLocale() );

   }
   public function testGetOptions()
   {

      $this->assertSame(
         [
            'locale' => $this->srcDe->getLocale(),
            'logger' => $this->log,
            'vfsManager' => null,
            'folder' => \dirname( \dirname( \dirname( __DIR__ ) ) ) . '/data/translations',
            'fileExtension' => 'php'
         ],
         $this->srcDe->getOptions()
      );

   }
   public function testGetOption()
   {

      $this->assertNull( $this->srcDe->getOption( 'vfsManager' ) );
      $this->assertFalse( $this->srcDe->getOption( 'foo' ) );
      $this->assertNull( $this->srcDe->getOption( 'bar', null ) );
      $this->assertSame( 'my://data/translations', $this->srcFr->getOption( 'folder' ) );

   }

}
