<?php

require_once(__DIR__ . '/' . 'HTTPException.php');

/**
 * @brief An exception indicating an error because of an HTTP bad request status code.
 */
class HTTPBadRequestException extends HTTPException {
  public function __construct(HTTPRequestContext $context) {
    parent::__construct($context);
  }
}; // class HTTPBadRequestException

?>

