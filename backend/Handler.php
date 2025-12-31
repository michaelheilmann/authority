<?php

/**
 * @brief The base class of all handlers.
 */
abstract class Handler {
  
  /** 
   * @param $context The context.
   * @param $requestPathParts Array of request path parts.
   * @param $requestMethod The request method.
   * @param $arguments The arguments.
   * @return HTTPResponse. null if no dispatch.
   */
  abstract public function dispatch(HTTPRequestContext $context, $requestPathParts, HTTPRequestMethod $requestMethod, $arguments) : HTTPResponse|null;
  
}; // class Handler

?>

