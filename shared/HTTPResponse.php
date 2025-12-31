<?php

require_once(__DIR__ . '/' . 'HTTPStatusCodes.php');
require_once(__DIR__ . '/' . 'JSONData.php');

/**
 * @brief Information on an HTTP response.
 */
class HTTPResponse {
  /** @brief The HTTP status code. */
  public HTTPStatusCodes $statusCode;

  /** @brief The data of the response or null. */
  public JSONData $data;
  
  /**
   * @brief Construct this HTTP response.
   * @param $statusCode The HTTP status code.
   * @param $data The data or null.
   */
  public function __construct(HTTPStatusCodes $statusCode, JSONData $data) {
    $this->statusCode = $statusCode;
    $this->data = $data;
  }

  public function getStatusCode() : HTTPStatusCodes {
    return $this->statusCode;
  }

  public function getData() : JSONData {
    return $this->data;
  }

};

?>

