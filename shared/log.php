<?php

/// @synopsis Base of error return values.
class MyError {
  private string|null $file;
  private string|null $line;

  /// @param string|null $file
  /// @param string|null $line
  public function __construct(string|null $file = null, string|null $line = null) {
    $this->file = $file;
    $this->line = $line;
  }

  /// @return string|null The file.
  public function getFile() : string|null {
    return $this->file;
  }
  
  /// @return string|null The line.
  public function getLine() : string|null {
    return $this->line;
  }
};

/// @synopsis Get if a value is of type MyError.
/// @param $v the value
function IsMyError($v) {
  return $v instanceof MyError;
}

/**
 * @synopsis Values of this type indicate an error.
 */
class My_Error {

  public $errors = array();

  /**
   * Initialize the error.
   * If `$code` is empty, the other parameters will be ignored.
   * If `$code` is not empty, `$message` will be used even if its
   * @param string|int $code The error code.
   * @param string           The error message.
   */
  public function __construct($code = '', $message = '') {
    if ($code === null || !is_string($code)) {
      $code = '';
    } 
    if ($message === null || !is_string($message)) {
      $message = '';
    }
    $this->errors[] = array($code, $message);
  }

  /**
   * Get all errors as a JSON list.
   * @code
   * '{' <errors> '}'
   * <errors> : <error> <errors-rest>
   *          | e
   * <errors-rest> = ',' <error> <errors-rest>
   *               |
   * @endcode
   */
  public function asJson() {
    return json_encode($this->errors);
  }

};

/// @synopsis Get if a value is of type My_Error.
/// @param $v the value
function My_isError($v) {
  return $v instanceof My_Error;
}


/// @brief Echo an error as JSON.
/// @param $message the message
/// @param $reason string or null
function My_error($message, $reason) {
  $error = array();
  $error['message'] = $message;
  if ($reason !== null) {
    $error['reason'] = $reason;
  }
  echo json_encode($error);
}

/// @brief Echo and error as JSON.
/// @param $message the message
function My_status($message) {
  $status = array();
  $status['message'] = $message;
  echo json_encode($status);
}


?>

