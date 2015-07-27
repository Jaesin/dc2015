<?php

/**
 * @file
 * Definition of Drupal\Core\ContentNegotiationInterface.
 */

namespace Drupal\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides content negotiation based upon query parameters.
 */
interface ContentNegotiationInterface {

  /**
   * Gets the normalized type of a request.
   *
   * The normalized type is a short, lowercase version of the format, such as
   * 'html', 'json' or 'atom'.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object from which to extract the content type.
   *
   * @return string
   *   The normalized type of a given request.
   */
  public function getContentType(Request $request);
}
