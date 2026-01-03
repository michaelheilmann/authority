<?php

/**
 * @brief The base class of all handlers.
 */
abstract class Handler {
  
  /** 
   * @param $context The HTTP request context.
   * @return HTTPResponse. null if no dispatch.
   */
  abstract public function dispatch(HTTPRequestContext $context) : HTTPResponse|null;
  
}; // class Handler

?>

