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

class ErrorMessageBuilder {
  private array $msg;
  
  public function __construct() {
    $this->msg = array('global-errors' => array(),
                       'field-errors' => array());
  }
  
  public function addGlobalError(string $error) {
    $this->msg['global-errors'][] = $error; 
  }
  
  public function addFieldError(string $field, string $error) {
    if (!isset($this->msg['field-errors'][$field])) {
      $this->msg['field-errors'][$field] = array();
    }
    $this->msg['field-errors'][$field][] = $error;
  }
  
  public function build() : array {
    return $this->msg;
  }

  public function hasFieldError(string $field) {
    return isset($this->msg['field-errors'][$field]);
  }    
   
  public function hasErrors() : bool {
   return count($this->msg['field-errors']) > 0
        || count($this->msg['global-errors']) > 0;
  }
  
};

?>

