<?php

require_once(__DIR__ . '/' . 'HTTPRequestMethod.php');

/**
 * @brief Information on an HTTP request.
 */
class HTTPRequestContext {
  /** @brief The request method. If we failed to determine the request method then this is null. */
  public HTTPRequestMethod|null $requestMethod;
  /* @brief The request path. */
  public array $requestPath;
  /* @brief The request arguments. */
  public array $requestArguments;
};

?>

