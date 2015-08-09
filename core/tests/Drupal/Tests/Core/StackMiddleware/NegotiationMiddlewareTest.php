<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\StackMiddleware\NegotiationMiddlewareTest.
 */

namespace Drupal\Tests\Core\StackMiddleware;

use Drupal\Core\StackMiddleware\NegotiationMiddleware;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @coversDefaultClass \Drupal\Core\StackMiddleware\NegotiationMiddleware
 * @group NegotiationMiddleware
 */
class NegotiationMiddlewareTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\StackMiddleware\NegotiationMiddleware
   */
  protected $negotiationMiddleware;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $dispatcher = new EventDispatcher();
    $resolver = new ControllerResolver();
    $app = new HttpKernel($dispatcher, $resolver);
    $this->negotiationMiddleware = $this->getMockBuilder('Drupal\Core\StackMiddleware\NegotiationMiddleware')
      ->setConstructorArgs(array($app))
      ->setMethods(array('getContentType'))
      ->getMock();

  }

  /**
   * Tests the getContentType() method with AJAX iframe upload.
   *
   * @covers ::getContentType
   */
  public function testAjaxIframeUpload() {
    $request = new Request();
    $request->attributes->set('ajax_iframe_upload', '1');

    $this->assertSame('iframeupload', $this->negotiationMiddleware->getContentType($request));
  }

  /**
   * Tests the specifying a format via query parameters gets used.
   */
  public function testFormatViaQueryParameter() {
    $request = new Request();
    $request->query->set('_format', 'bob');

    $this->assertSame('bob', $this->negotiationMiddleware->getContentType($request));
  }

  /**
   * Tests the getContentType() method when no priority format is found.
   *
   * @covers ::getContentType
   */
  public function testUnknowContentTypeReturnsHtmlByDefault() {
    $request = new Request();

    $this->assertSame('html', $this->negotiationMiddleware->getContentType($request));
  }

  /**
   * Tests the getContentType() method when no priority format is found but it's an AJAX request.
   *
   * @covers ::getContentType
   */
  public function testUnknowContentTypeButAjaxRequest() {
    $request = new Request();
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');

    $this->assertSame('html', $this->negotiationMiddleware->getContentType($request));
  }

}
