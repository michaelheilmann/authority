<?php

require_once(__DIR__ . '/../backend/' . "Handler.php");

/**
 * @brief Handler for `api/login<rest>` requests.
 */
class LoginHandler extends Handler {

  protected $mysqli;

  /**
   * @brief Construct this handler.
   */
  public function __construct() {
    $this->mysqli = null;
  }
  
  private function login(string $name, string $password) : bool {
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
      return false;
    }
   $row = mysqli_fetch_assoc($rows);
   $hashedPassword = $row['password'];
   return password_verify($password, $hashedPassword);
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
      if ($context->requestPath[0] !== 'login') {
        error_log("path is not ['login']", 0);        
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
      // (1)
      if (!isset($context->requestBody['name'])) {
        $emb->addFieldError('name', 'please specify a name');
      }
      if (!$emb->hasFieldError('name')) {
        $name = $context->requestBody['name'];
      }
      // (2)
      if (!isset($context->requestBody['password'])) {
        $emb->addFieldError('password', 'please specify a password');
      }
      if (!$emb->hasFieldError('password')) {
        $password = $context->requestBody['password'];
      }

      // Defer error propagation as far as possible.
      if ($emb->hasErrors()) {
        error_log("bad request", 0);  
        return new HTTPResponse(HTTPStatusCode::BAD_REQUEST, JSONData::encode($emb->build()));
      }
      
      // Check credentials.
      // Return 'invalid user name or password' / 400 / bad request if validation fails.
      if (!$this->login($name, $password)) {
        error_log("unauthorized", 0);
        $emb->addGlobalError('invalid name or password');
        return new HTTPResponse(HTTPStatusCode::UNAUTHORIZED, JSONData::encode($emb->build()));
      }
      error_log("authorized", 0);
      return new HTTPResponse(HTTPStatusCode::OK, JSONData::encode(array()));     
    } catch (Exception $e) {
     /* exceptions yield 500 / internal server error */
     error_log("internal server error: " . $e, 0);
     return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
    }
  }

} // class LoginHandler

?>
