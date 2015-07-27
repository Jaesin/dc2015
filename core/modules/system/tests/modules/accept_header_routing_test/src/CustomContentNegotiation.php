<?php

/**
 * @file
 * Definition of Drupal\accept_header_routing_test\CustomContentNegotiation.
 */

namespace Drupal\accept_header_routing_test;

use Drupal\Core\ContentNegotiationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides content negotiation based upon query parameters and the accept header.
 */
class CustomContentNegotiation implements ContentNegotiationInterface {

  /**
   * {@inheritdoc}
   */
  public function getContentType(Request $request) {
    // AJAX iframe uploads need special handling, because they contain a JSON
    // response wrapped in <textarea>.
    if ($request->get('ajax_iframe_upload', FALSE)) {
      return 'iframeupload';
    }

    if ($request->query->has('_format')) {
      return $request->query->get('_format');
    }

    // Create accept header map.
    $type_map = [
      'application/json' => 'json',
      'application/hal+json' => 'hal_json',
      'application/xml' => 'xml',
      'text/html' => 'html',
    ];

    // Get the first accept header.
    $accept = explode(',', $request->headers->get('Accept'));

    // Check to see if the accept header is in out list.
    if (isset($type_map[$accept[0]])) {
      return $type_map[$accept[0]];
    }

    if ($request->isXmlHttpRequest()) {
      return 'ajax';
    }

    // Do HTML last so that it always wins.
    return 'html';
  }
}
