<?php

/**
 * @brief Constants for HTTP request methods.
 */
enum HTTPRequestMethod {
  case Get;
  case Post;
  case Patch;
  case Put;
  
  public static function toString($enumValue) {
    switch ($enumValue) {
      case self::Get:
        return 'GET';
      case self::Post:
        return 'POST';
      case self::Patch:
        return 'PATCH';
      case self::Put:
        return 'PUT';
      default:
        return '<unknown/unsupported request method>';
    };
  }

};

?>

