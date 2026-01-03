<?php

require_once(__DIR__ . '/../backend/' . "Handler.php");

/**
 * @brief Handler for `api/organizations<rest>` requests.
 */
class OrganizationsHandler extends Handler {

  protected $mysqli;

  /**
   * @brief Construct this handler.
   */
  public function __construct() {
    $this->mysqli = null;
  }

  /**
   * @brief Get the number of organizations.
   * @param $context The context.
   * @return The number of organizations (non-negative int) on success, null on failure
   */
  private function getCount(HTTPRequestContext $context) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        return null;
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT COUNT(*) as `numberOfElements` FROM `organizations`");
    if ($rows === false) {
      return null;
    }
    $row = mysqli_fetch_assoc($rows);
    return toInt($row['numberOfElements']);
  }
  
  /**
   * @brief Get the database ID of a node given its unique ID.
   * @param $uniqueID The unique ID of the node.
   * @return The node database ID or null.
   */
  private function getNodeID(HTTPRequestContext $context, $uniqueID) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        /* database failure or inconsistencies yield 500 / internal server error */
        return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT `id` FROM `nodes` WHERE `nodes`.`unique-id`='" . $uniqueID . "'");
    if ($rows === false) {
      /* database failure or inconsistencies yield 500 / internal server error */
      return new HTTPResponse(HTTPStatusCode::NOT_FOUND, JSONData::encode(array()));
    }
    $row = mysqli_fetch_assoc($rows);
    if ($row !== null) {
      $id = $row['id'];
      return $id;
    } else {
      return null;
    }
  }

  /**
   * @brief Get the tags of an organization.
   * @param $id The unique ID of the node.
   * @return The tags of the organization.
   */
  public function getTags($context, $uniqueID) : HTTPResponse|null {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        /* database failure or inconsistencies yield 500 / internal server error */
        return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
      }
    }
    $nodeID = $this->getNodeID($context, $uniqueID);
    if ($nodeID === null) {
      /* entity not found yields 404 / not found */
      return new HTTPResponse(HTTPStatuscode::NOT_FOUND, JSONData::encode(array()));
    }
    $rows = mysqli_query($this->mysqli, "SELECT * FROM `tags-nodes` as lhs LEFT JOIN `tags` as rhs on lhs.`tag-id` = rhs.id WHERE lhs.`node-id`='" . $nodeID . "'");
    if ($rows === false) {
      /* database failure or inconsistencies yield 500 / internal server error */
      return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
    }
    $tags = array();
    while ($row = mysqli_fetch_assoc($rows)) {
      $tag = array('node-id' => $row['node-id'], 'tag-id' => $row['tag-id'], 'name' => $row['name']);
      $tags[] = $tag;
    }
    return new HTTPResponse(HTTPStatusCode::OK, JSONData::encode($tags));  
  }

  /**
   * @brief Get all organizations.
   * @return
   * - on success: HTTPResponse with status code "ok", the data of the organization
   * - if the organization was not found: HTTPResponse with status code "not found", empty array as data
   * - if any other error occurs: HTTPResponse with some status code, empty array as data
   * @todo Remove the database ID from the return data.
   */
  public function findAll($context, $index, $count) : HTTPResponse|null {
    if (!is_int($index) || !is_int($count)) {
      /* invalid argument values yield 400 / bad request */
      return new HTTPResponse(HTTPStatusCode::BAD_REQUEST, JSONData::encode(array()));
    }
    if ($index < 0 || $count < 0) {
      /* invalid argument values yield 400 / bad request */
      return new HTTPResponse(HTTPStatusCode::BAD_REQUEST, JSONData::encode(array()));
    }
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        /* database failure or inconsistencies yield 500 / internal server error */
        return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT * FROM `organizations` ORDER BY `name` LIMIT " . $index . ", " . $count);
    if ($rows === false) {
      /* database failure or inconsistencies yield 500 / internal server error */
      return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
    }
    $organizations = array();
    while ($row = mysqli_fetch_assoc($rows)) {
      $organization = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'name' => $row['name']);
      $organizations[] = $organization;
    }
    $response = array('numberOfElements' => $this->getCount($context), 'elements' => $organizations);
    return new HTTPResponse(HTTPStatusCode::OK, JSONData::encode($response));
  }

  /**
   * @brief Get the organization of the specified unique ID.
   * @param $uniqueID The unique ID.
   * @return
   * - if the organization was found: HTTPResponse with status code "ok", the data of the organization
   * - if the organization was not found: HTTPResponse with status code "not found", empty array as data
   * - if any other error occurs: HTTPResponse with some status code, empty array as data
   * @todo Remove the database ID from the return data.
   */
  public function find($context, $uniqueID) : HTTPResponse|null {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        /* database failure or inconsistencies yield 500 / internal server error */
        return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
      }
    }
   $rows = mysqli_query($this->mysqli, "SELECT * FROM FROM `organizations` INNER JOIN `nodes` ON `organizations`.`node-id` = `nodes`.`id`"
                                       . " " . "WHERE `organizations`.`unique-id`='" . $this->mysqli->real_escape_string($uniqueID) . "'");
    if ($rows === false || $rows->num_rows > 1) {
     /* database failure or inconsistencies yield 500 / internal server error */
     return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
    }
    if ($rows->num_rows == 0) {
     /* entity not found yields 404 / not found */
     return new HTTPResponse(HTTPStatuscode::NOT_FOUND, JSONData::encode(array()));
    }
    $row = mysqli_fetch_assoc($rows);
    $response = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'name' => $row['name']);
    return HTTPResponse(HTTPStatusCode::OK, JSONData::encode($response));
  }

  /**@override*/
  public function dispatch(HTTPRequestContext $context) : HTTPResponse|null {
    try {
      if ($context->requestMethod !== HTTPRequestMethod::Get) {
        return null;
      }
      // persons
      if (count($context->requestPath) == 1) {
        if ($context->requestPath[0] == 'organizations') {      
          $numberOfArguments = count($context->requestArguments);
          if ($numberOfArguments == 0) {
            return $this->findAll($context, 0, $this->getCount($context));
          } else if (isset($context->requestArguments['index']) && isset($context->requestArguments['count']) && $numberOfArguments == 2) {
            return $this->findAll($context, toInt($context->requestArguments['index']), toInt($context->requestArguments['count']));
          }
        } 
      } else if (count($context->requestPath) == 2) {
        if ($context->requestPath[0] == 'organizations') {      
          $numberOfArguments = count($context->requestArguments);
          if ($numberOfArguments == 0) {
            return $this->find($context, $context->requestPath[1]);
          }
        } 
      } else if (count($requestPathParts) == 3) {
        if ($requestPathParts[0] == 'organizations' && $context->requestPath[2] == 'tags') {      
          $numberOfArguments = count($context->requestArguments);
          if ($numberOfArguments == 0) {
            return $this->getTags($context, $requestPathParts[1]);
          }
        } 
      }
      return null;
    } catch (Exception $e) {
     /* exceptions yield 500 / internal server error */
     return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
    }
  }

} // class PersonsHandler


?>

