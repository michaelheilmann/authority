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
   * @return JSON on success. null if no dispatch. exception on failure during dispatch.
   */
  abstract public function dispatch(HTTPRequestContext $context, $requestPathParts, HTTPRequestMethod $requestMethod, $arguments) : JSONData|null;
  
}; // class Handler

?>

