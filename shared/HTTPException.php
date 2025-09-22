<?php

require_once(__DIR__ . '/' . 'HTTPException.php');
require_once(__DIR__ . '/' . 'HTTPRequestContext.php');

/**
 * @brief Exception indicating an error because an HTTP status code.
 */
class HTTPException extends Exception {
  private string|null $requestMethod;
  private array|null $requestPath;
  private array|null $requestArguments;
  protected function __construct(HTTPRequestContext $context) {
    $this->requestMethod = $context->requestMethod;
    $this->requestPath = $context->requestPath;
    $this->requestArguments = $context->requestArguments;
  }
}; // class HTTPException

?>

