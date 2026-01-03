<?php

/// Syntactical validation of user names, user passwords, and user email addresses.
class Validator {

  /// Validate a user password.
  /// @param string $input The user password.
  /// @return bool true if valid. false if invalid.
  public static function validateUserPassword(string $input) : bool {
    $uppercase = preg_match('@[A-Z]@', $input);
    $lowercase = preg_match('@[a-z]@', $input);
    $number    = preg_match('@[0-9]@', $input);
    $special   = preg_match('@[^\w]@', $input);
    return $uppercase && $lowercase && $number && $special;
  }

  /// Validate a user name.
  /// @param string $input The user name.
  /// @return bool true if valid. false if invalid.
  public static function validateUserName(string $input) : bool {
    /// @remarks The current user name syntactic form is: at least 6 characters, alphabetic, alphanumeric, or numeric only. Must start with an alphabetic.
    return 1 === preg_match("/^[a-zA-Z][a-zA-Z0-9]{5,}$/", $input);
  }

  /// Validate a user email.
  /// @param string $input The user email.
  /// @return bool true if valid. false if invalid.
  public static function validateUserEmail(string $input) : bool {
    /// @rmearks The current user email syntactic form is specified here https://html.spec.whatwg.org/#e-mail-state-(type=email).
    $pattern = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}'
             . '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD'
             ;
    return 1 === preg_match($pattern, $input);
  }

};

?>

