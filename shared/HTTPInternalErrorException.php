<?php

require_once(__DIR__ . '/' . 'HTTPException.php');

/**
 * @brief An exception indicating an error because of an HTTP internal error status code.
 */
class HTTPInternalErrorException extends HTTPException {
  public function __construct(HTTPRequestContext $context) {
    parent::__construct($context);
  }
}; // class HTTPInternalErrorException

?>

