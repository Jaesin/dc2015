<?php

/**
 * @file
 * Contains \Drupal\Core\ContentNegotiation.
 */

namespace Drupal\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides content negotation based upon query parameters.
 */
class ContentNegotiation implements ContentNegotiationInterface {

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

    // Do HTML last so that it always wins.
    return 'html';
  }
}
