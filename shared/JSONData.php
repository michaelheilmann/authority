<?php

/**
 * Allows for type-encoding that data is valid JSON.
 */
class JSONData {

  private string $data;

  protected function __construct(string $data) {
    $this->data = $data;
  }
  
  /**
   * @brief Get the data.
   * @return The data. Guaranteed to be valid JSON. 
   */
  public function getData() : string {
    return $this->data;
  }
 
  private static function toUTF8($mixed) {
    if (is_array($mixed)) {
      foreach ($mixed as $key => $value) {
        $mixed[$key] = JSONData::toUTF8($value);
      }
    } else if (is_string ($mixed)) {
      return utf8_encode($mixed);
    }
    return $mixed;
  }
  
  /**
   * @brief Encode a string or an array.
   * @return The JSONData.
   */
  public static function encode(string|array|int $data) : JSONData {
    $encodedData = json_encode(JSONData::toUTF8($data), JSON_UNESCAPED_SLASHES);
    if ($encodedData === false) {
      switch (json_last_error()) {
        case JSON_ERROR_DEPTH:
          throw new Error("JSON encoding failed: " . 'maximum stack depth exceeded');
        case JSON_ERROR_STATE_MISMATCH:
          throw new Error("JSON encoding failed: " . 'underflow or the modes mismatch');
        case JSON_ERROR_CTRL_CHAR:
          throw new Error("JSON encoding failed: " . 'unexpected control character found');
        case JSON_ERROR_SYNTAX:
          throw new Error("JSON encoding failed: " . 'syntax error, malformed JSON');
        default:
          throw new Error("JSON encoding failed: " . "unknown/unexpected error code " . json_last_error());
      };
    }
    return new JSONData($encodedData);
  }
};

?>

