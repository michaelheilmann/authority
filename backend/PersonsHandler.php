<?php

require_once(__DIR__ . '/../backend/' . "Handler.php");

/**
 * @brief Handler for `api/persons<rest>` requests.
 */
class PersonsHandler extends Handler {

  protected $mysqli;

  /**
   * @brief Construct this gateway.
   */
  public function __construct() {
    $this->mysqli = null;
  }
  
  /**
   * @brief Get the node ID of a node given its unique ID.
   * @param $uniqueID The unique ID of the node.
   * @return The node ID or null.
   */
  public function getNodeID($context, $uniqueID) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        throw new HTTPInternalErrorException($context);
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT `id` FROM `nodes` WHERE `nodes`.`unique-id`='" . $uniqueID . "'");
    if ($rows === false) {
      return null;
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
  public function getTags($context, $uniqueID) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        throw new HTTPInternalErrorException($context);
      }
    }
    $nodeID = $this->getNodeID($context, $uniqueID);
    if ($nodeID === null) {
      throw new HTTPBadRequestException($context); // TODO: Should be "404 Not Found".
    }
    $rows = mysqli_query($this->mysqli, "SELECT * FROM `tags_nodes` as lhs LEFT JOIN `tags` as rhs on lhs.`tag-id` = rhs.id WHERE lhs.`node-id`='" . $nodeID . "'");
    if ($rows === false) {
      throw new HTTPInternalErrorException($context); 
    }
    $tags = array();
    while ($row = mysqli_fetch_assoc($rows)) {
      $tag = array('id' => $row['id'], 'node-id' => $row['node-id'], 'tag-id' => $row['tag-id'], 'name' => $row['name']);
      $tags[] = $tag;
    }
    return $tags;   
  }

  /**
   * @brief Get the number of organizations.
   * @return The number of organizations.
   * @throw HTTPInternalErrorException unable to open database connection
   */
  public function getCount($context) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        throw new HTTPInternalErrorException($context);
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT COUNT(*) as `numberOfPersons` FROM `persons`");
    if ($rows === false) {
      throw new HTTPInternalErrorException($context);   
    }
    $row = mysqli_fetch_assoc($rows);
    return toInt($row['numberOfPersons']);
  }

  /**
   * @brief Get all persons.
   * @return All persons.
   * @throw ApiException
   */
  public function findAll($context, $index, $count) {
    if (!is_int($index) || !is_int($count)) {
      throw new HTTPBadRequestException($context);
    }
    if ($index < 0 || $count < 0) {
      throw new HTTPBadRequestException($context);
    }
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        throw new HTTPInternalErrorException($context);
      }
    }
    try {
      $rows = mysqli_query($this->mysqli, "SELECT * FROM `persons` INNER JOIN `nodes` ON `persons`.`node-id` = `nodes`.`id`"
                                          . " " . "ORDER BY `persons`.`surname`, `persons`.`prename` LIMIT " . $index . ", " . $count);
      if ($rows === false) {
        throw new HTTPBadRequestException($context);
      }
      $persons = array();
      while ($row = mysqli_fetch_assoc($rows)) {
        $person = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'prename' => $row['prename'], 'surname' => $row['surname']);
        $persons[] = $person;
      }
      $response = array('numberOfElements' => $this->getCount($context), 'elements' => $persons);
      return $response;
    } catch (HTTPException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new HTTPInternalErrorException($context);
    }
  }

  /**
   * @brief Get the person of the specified ID.
   * @param $uniqueId The unique ID.
   * @return The person of the specified ID if it exists. null otherwise
   * @throw ApiException
   */
  public function find($context, $uniqueID) {
   if ($this->mysqli === null) {
     $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
     if ($this->mysqli->connect_errno) {
       throw new HTTPInternalErrorException($context);
     }
   }
   $rows = mysqli_query($this->mysqli, "SELECT * FROM FROM `persons` INNER JOIN `nodes` ON `persons`.`node-id` = `nodes`.`id`"
                                       . " " . "WHERE `persons`.`unique-id`='" . $this->mysqli->real_escape_string($uniqueID) . "'");
   if ($rows === false || $rows->num_rows > 1) {
     throw new HTTPBadRequestException($context);
   }
   if ($rows->num_rows == 0) {
     return null;
   }
   $row = mysqli_fetch_assoc($rows);
   $person = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'prename' => $row['prename'], 'surname' => $row['surname']);
   return $person;
  }

  // @param $requestPathParts 
  // return json on success. null if no dispatch. exception on failure during dispatch.
  public function dispatch($context, $requestPathParts, $requestMethod, $arguments) {
    if ($requestMethod !== HTTPRequestMethod::Get) {
      return null;
    }
    // persons
    if (count($requestPathParts) == 1) {
      if ($requestPathParts[0] == 'persons') {      
        $numberOfArguments = count($arguments);
        if ($numberOfArguments == 0) {
          return $this->findAll($context, 0, $this->getCount($context));
        } else if (isset($arguments['index']) && isset($arguments['count']) && $numberOfArguments == 2) {
          return $this->findAll($context, toInt($arguments['index']), toInt($arguments['count']));
        }
      } 
    } else if (count($requestPathParts) == 2) {
      if ($requestPathParts[0] == 'persons') {      
        $numberOfArguments = count($arguments);
        if ($numberOfArguments == 0) {
          return $this->find($context, $requestPathParts[1]);
        }
      } 
    } else if (count($requestPathParts) == 3) {
      if ($requestPathParts[0] == 'persons' && $requestPathParts[2] == 'tags') {      
        $numberOfArguments = count($arguments);
        if ($numberOfArguments == 0) {
          return $this->getTags($context, $requestPathParts[1]);
        }
      } 
    }
    return null;
  }

} // class PersonsHandler

?>

