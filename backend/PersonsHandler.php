<?php

require_once(__DIR__ . '/../backend/' . "Handler.php");

/**
 * @brief Handler for `api/persons<rest>` requests.
 */
class PersonsHandler extends Handler {

  protected $mysqli;

  /**
   * @brief Construct this handler.
   */
  public function __construct() {
    $this->mysqli = null;
  }
  
  /**
   * @brief Get the number of persons.
   * @param $context The context.
   * @return The number of persons (non-negative int) on success, null on failure
   */
  private function getCount(HTTPRequestContext $context) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        return null;
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT COUNT(*) as `numberOfElements` FROM `persons`");
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
   * @brief Get the tags of a node.
   * @param $uniqueID The unique ID of the node.
   * @return The tags of the node.
   */
  public function getTags(HTTPRequestContext $context, $uniqueID) : HTTPResponse|null {
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
   * @brief Get all persons.
   * @return 
   * 200 status code: data of HTTP result object is the array of persons
   * non-200 status code: data of HTTP result object is the empty array
   * @todo Remove the database ID from the return data.
   */
  public function findAll(HTTPRequestContext $context, $index, $count) : HTTPResponse|null {
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
    $rows = mysqli_query($this->mysqli, "SELECT * FROM `persons` INNER JOIN `nodes` ON `persons`.`node-id` = `nodes`.`id`"
                                        . " " . "ORDER BY `persons`.`surname`, `persons`.`prename` LIMIT " . $index . ", " . $count);
    if ($rows === false) {
      /* database failure or inconsistencies yield 500 / internal server error */
      return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
    }
    $persons = array();
    while ($row = mysqli_fetch_assoc($rows)) {
      $person = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'prename' => $row['prename'], 'surname' => $row['surname']);
      $persons[] = $person;
    }
    $response = array('numberOfElements' => $this->getCount($context), 'elements' => $persons);
    return new HTTPResponse(HTTPStatusCode::OK, JSONData::encode($response));
  }

  /**
   * @brief Get the person of the specified unique ID.
   * @param $uniqueID The unique ID.
   * @return
   * - if the person was found: HTTPResponse with status code "ok", the data of the person
   * - if the person was not found: HTTPResponse with status code "not found", empty array as data
   * - if any other error occurs: HTTPResponse with some status code, empty array as data
   * @todo Remove the database ID from the return data.
   */
  public function find(HTTPRequestContext $context, $uniqueID) : HTTPResponse|null {
   if ($this->mysqli === null) {
     $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
     if ($this->mysqli->connect_errno) {
        /* database failure or inconsistencies yield 500 / internal server error */
        return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
     }
   }
   $rows = mysqli_query($this->mysqli, "SELECT * FROM FROM `persons` INNER JOIN `nodes` ON `persons`.`node-id` = `nodes`.`id`"
                                       . " " . "WHERE `persons`.`unique-id`='" . $this->mysqli->real_escape_string($uniqueID) . "'");
   if ($rows === false || $rows->num_rows > 1) {
     /* database failure or inconsistencies yield 500 / internal server error */
     return new HTTPResponse(HTTPStatusCode::INTERNAL_ERROR, JSONData::encode(array()));
   }
   if ($rows->num_rows == 0) {
     /* entity not found yields 404 / not found */
     return new HTTPResponse(HTTPStatusCode::NOT_FOUND, JSONData::encode(array()));
   }
   $row = mysqli_fetch_assoc($rows);
   $result = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'prename' => $row['prename'], 'surname' => $row['surname']);
   return new HTTPResponse(HTTPStatusCode::OK, JSONData::encode($result));
  }

  /**@override*/
  public function dispatch(HTTPRequestContext $context) : HTTPResponse|null {
    try {
      if ($context->requestMethod !== HTTPRequestMethod::Get) {
        return null;
      }
      // persons
      if (count($context->requestPath) == 1) {
        if ($context->requestPath[0] == 'persons') {      
          $numberOfArguments = count($context->requestArguments);
          if ($numberOfArguments == 0) {
            return $this->findAll($context, 0, $this->getCount($context));
          } else if (isset($context->requestArguments['index']) && isset($context->requestArguments['count']) && $numberOfArguments == 2) {
            return $this->findAll($context, toInt($context->requestArguments['index']), toInt($context->requestArguments['count']));
          }
        } 
      } else if (count($context->requestPath) == 2) {
        if ($context->requestPath[0] == 'persons') {      
          $numberOfArguments = count($context->requestArguments);
          if ($numberOfArguments == 0) {
            return $this->find($context, $context->requestPath[1]);
          }
        } 
      } else if (count($context->requestPath) == 3) {
        if ($context->requestPath[0] == 'persons' && $context->requestPath[2] == 'tags') {      
          $numberOfArguments = count($context->requestArguments);
          if ($numberOfArguments == 0) {
            return $this->getTags($context, $context->requestPath[1]);
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
