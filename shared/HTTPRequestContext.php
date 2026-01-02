<?php

require_once(__DIR__ . '/' . 'HTTPRequestMethod.php');

/**
 * @brief Information on an HTTP request.
 */
class HTTPRequestContext {
  
  public function __construct(HTTPRequestMethod $requestMethod, array $requestPath, array $requestArguments, string|null $requestBody) {
    $this->requestMethod = $requestMethod;
    $this->requestPath = $requestPath;
    $this->requestArguments = $requestArguments;
    $this->requestBody = $requestBody;
  }
  
  /**
   * @brief
   * The request method.
   */
  public HTTPRequestMethod $requestMethod;
  
  /**
   * @brief
   * The request path.
   */
  public array $requestPath;
  
  /**
   * @brief
   * The request arguments.
   * @detail
   * Associative key/value array. Always empty for non-get requests.
   */
  public array $requestArguments;
  
  /** 
   * @brief
   * The request body.
   * null if there is no request body.
   * @todo Should be JSONData.
   */
  public string|null $requestBody;
  
  public function toString() {
    $msg = 'request method: ' . HTTPRequestMethod::toString($this->requestMethod) . '\n';
    $msg = $msg . 'number of arguments: ' . count($this->requestArguments) . '\n';
    $msg = $msg . '[\n';
    foreach ($this->requestArguments as $k => $v) {
      $msg = $msg . ' ' . $k . ' => ' . $v . ',\n';
    }
    $msg = $msg . '],\n';
    $msg = $msg . 'request Body: ' . $this->requestBody;
    return $msg;
  }
};

?>

