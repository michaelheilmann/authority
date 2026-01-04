<?php

require_once(__DIR__ . '/../backend/' . "Handler.php");

/**
 * @brief Handler for `api/register<rest>` requests.
 */
class RegisterHandler extends Handler {

  protected $mysqli;

  /**
   * @brief Construct this handler.
   */
  public function __construct() {
    $this->mysqli = null;
  }

  private function isNameTaken(string $name) : bool {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
          /* database failure or inconsistencies yield 500 / internal server error; as the return value does not allow for HTTPResponse, we use an exception which is then mappend to  500 / internal server error. */
          error_log("unable to establish database connection", 0);
          throw new Error('unable to establish database connection');
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT * FROM `users`"
                                       . " "
                                       . "WHERE `users`.`name`='" . $this->mysqli->real_escape_string($name) . "'");
    if ($rows === false || $rows->num_rows > 1) {
      /* database failure or inconsistencies yield 500 / internal server error; as the return value does not allow for HTTPResponse, we use an exception which is then mappend to  500 / internal server error. */
      error_log("unable to establish database connection", 0);
      throw new Error('unable to establish database connection');
    }
    if ($rows->num_rows == 0) {
      error_log("no entries found", 0);
      return false;
    }
    return true;
  }

  private function isEmailTaken(string $email) : bool {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
          /* database failure or inconsistencies yield 500 / internal server error; as the return value does not allow for HTTPResponse, we use an exception which is then mappend to  500 / internal server error. */
          throw new Error('unable to establish database connection');
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT * FROM `users`"
                                       . " "
                                       . "WHERE `users`.`email`='" . $this->mysqli->real_escape_string($email) . "'");
    if ($rows === false || $rows->num_rows > 1) {
      /* database failure or inconsistencies yield 500 / internal server error; as the return value does not allow for HTTPResponse, we use an exception which is then mappend to  500 / internal server error. */
      throw new Error('unable to establish database connection');
    }
    if ($rows->num_rows == 0) {
      return false;
    }
    return true;
  }

  /**@override*/
  public function dispatch(HTTPRequestContext $context) : HTTPResponse|null {
    try {
      $emb = new ErrorMessageBuilder();
      if ($context->requestMethod !== HTTPRequestMethod::Post) {
        error_log("not a post request", 0);
        return null;
      }
      if (count($context->requestPath) !== 1) {
        error_log("request path parts # <> 1", 0);
        return null;
      }
      if ($context->requestPath[0] !== 'register') {
        error_log("path is not ['register']", 0);
        return null;
      }
      $numberOfArguments = count($context->requestArguments);
      if ($numberOfArguments !== 0) {
        error_log("number of arguments is not null", 0);
        return new HTTPResponse(HTTPStatusCode::BAD_REQUEST, JSONData::encode(array('error' => 'number of arguments is not null')));
      }
      if ($context->requestBody === null) {
        error_log("request body is null", 0);
        return new HTTPResponse(HTTPStatusCode::BAD_REQUEST, JSONData::encode(array('error' => 'request body is null')));
      }
      
      // Assert 'name' is provided.
      if (!isset($context->requestBody['name'])) {
        $emb->addFieldError('name', 'please specify a name');
      }
      if (!$emb->hasFieldError('name')) {
        $name = $context->requestBody['name'];
      }
      // Assert 'password' is provided.
      if (!isset($context->requestBody['password'])) {
        $emb->addFieldError('password', 'please specify a password');
      }
      if (!$emb->hasFieldError('password')) {
        $password = $context->requestBody['password'];
      }
      // Asert 'email' is provided'.
      if (!isset($context->requestBody['email'])) {
        $emb->addFieldError('email', 'please specify an email');
      }
      if (!$emb->hasFieldError('email')) {
        $email = $context->requestBody['email'];
      }
      
      
      if ($this->mysqli === null) {
        $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
        if ($this->mysqli->connect_errno) {
          /* database failure or inconsistencies yield 500 / internal server error */
          return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
        }
      }
      
      // Assert 'name' is valid and not taken.
      if (!$emb->hasFieldError('name')) {
        if (!Validator::validateUserName($name)) {
          $emb->addFieldError('name', 'name must be at least 6 characters long, may only contain letters and digits, and must start with letter');
        }
      }
      if (!$emb->hasFieldError('name')) {
        if ($this->isNameTaken($name)) {
          $emb->addFieldError('name', 'name already taken');
        }
      }
      // Assert 'password' is valid.
      if (!$emb->hasFieldError('password')) {
        if (!Validator::validateUserPassword($password)) {
          error_log("'password' is not valid", 0);
          $emb->addFieldError('password', 'password must be at least 8 characters in length'
                                        . ' ' . 'and should include at least one upper case letter, one number, and one special character.');
        }
      }
      // Assert 'email' is valid.
      if (!$emb->hasFieldError('email')) {
        if (!Validator::validateUserEmail($email)) {
          $emb->addFieldError('email', 'email is not valid');
        }
      }
      if (!$emb->hasFieldError('email')) {
        if ($this->isEmailTaken($email)) {
          $emb->addFieldError('email', 'email already in use');
        }
      }
      


      // Defer error propagation as far as possible.
      if ($emb->hasErrors()) {
        return new HTTPResponse(HTTPStatusCode::BAD_REQUEST, JSONData::encode($emb->build()));
      }

      // hash the password
      // TODO: Obviously illicit approach. Add salt.
      // TODO: We need to centralize this.
      $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
      error_log("registering $name with $password", 0);
      error_log("hashed password: $hashedPassword", 0);

      // Create the file entry.
      if (mysqli_query($this->mysqli, "INSERT INTO `users` (`id`, `name`, `password`, `email`) VALUES (NULL, '"
                             . $this->mysqli->real_escape_string($name) . "', '"
                             . $this->mysqli->real_escape_string($hashedPassword) . "', '"
                             . $this->mysqli->real_escape_string($email) . "')") === false) {
        error_log("failed to insert user (*)", 0);
        /* database failure or inconsistencies yield 500 / internal server error */
        return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
      }
      $id = mysqli_insert_id($this->mysqli);
      if (0 === $id) {
        error_log("failed to insert user (**)", 0);
        /* database failure or inconsistencies yield 500 / internal server error */
        return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
      }
      /* Success. */
      return new HTTPResponse(HTTPStatusCode::OK, JSONData::encode(array()));
    } catch (Exception $e) {
     /* exceptions yield 500 / internal server error */
     return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
    }
  }

} // class RegisterHandler

?>
