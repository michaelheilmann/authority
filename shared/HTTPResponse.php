<?php

require_once(__DIR__ . '/' . 'HTTPStatusCode.php');
require_once(__DIR__ . '/' . 'JSONData.php');

/**
 * @brief Information on an HTTP response.
 */
class HTTPResponse {
  /** @brief The HTTP status code. */
  public HTTPStatusCode $statusCode;

  /** @brief The data of the response or null. */
  public JSONData $data;
  
  /**
   * @brief Construct this HTTP response.
   * @param $statusCode The HTTP status code.
   * @param $data The data or null.
   */
  public function __construct(HTTPStatusCode $statusCode, JSONData $data) {
    $this->statusCode = $statusCode;
    $this->data = $data;
  }

  public function getStatusCode() : HTTPStatusCode {
    return $this->statusCode;
  }

  public function getData() : JSONData {
    return $this->data;
  }

};

?>

