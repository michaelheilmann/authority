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
  
  /**
   * @brief Encode a string or an array.
   * @return The JSONData.
   */
  public static function encode(string|array|int $data) : JSONData {
    $encodedData = json_encode($data, JSON_UNESCAPED_SLASHES);
    if ($encodedData === false) {
      throw new Error("JSON encoding failed: " + json_last_error());  
    }
    return new JSONData($encodedData);
  }
};

?>

