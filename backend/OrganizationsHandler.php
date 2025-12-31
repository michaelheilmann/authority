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
   * @brief Get the tags of an organization.
   * @param $id The unique ID of the organization.
   * @return The tags of the organization.
   */
  public function getTags($context, $id) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        throw new HTTPInternalErrorException($context);
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT * FROM `tags_persons` as lhs LEFT JOIN `tags` as rhs on lhs.tag_id = rhs.id WHERE lhs.person_id='" . $id . "'");
    if ($rows === false) {
      throw new HTTPInternalErrorException($context); 
    }
    $tags = array();
    while ($row = mysqli_fetch_assoc($rows)) {
      $tag = array('id' => $row['id'], 'person_id' => $row['person_id'], 'tag_id' => $row['tag_id'], 'name' => $row['name']);
      $tags[] = $tag;
    }
    return $tags;   
  }

  /**
   * @brief Get the number of persons.
   * @return The number of persons.
   * @throw ApiException
   */
  public function getCount($context) {
    if ($this->mysqli === null) {
      $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
      if ($this->mysqli->connect_errno) {
        throw new HTTPInternalErrorException($context);
      }
    }
    $rows = mysqli_query($this->mysqli, "SELECT COUNT(*) as `numberOfElements` FROM `organizations`");
    if ($rows === false) {
      throw new HTTPInternalErrorException($context);   
    }
    $row = mysqli_fetch_assoc($rows);
    return toInt($row['numberOfElements']);
  }

  /**
   * @brief Get all organizations.
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
      $rows = mysqli_query($this->mysqli, "SELECT * FROM `organizations` ORDER BY `name` LIMIT " . $index . ", " . $count);
      if ($rows === false) {
        throw new HTTPBadRequestException($context);
      }
      $persons = array();
      while ($row = mysqli_fetch_assoc($rows)) {
        $person = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'name' => $row['name']);
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
   * @brief Get the organization of the specified ID.
   * @param $uniqueId The unique ID.
   * @return The organization of the specified ID if it exists. null otherwise
   * @throw ApiException
   */
  public function find($context, $id) {
   if ($this->mysqli === null) {
     $this->mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
     if ($this->mysqli->connect_errno) {
       throw new HTTPInternalErrorException($context);
     }
   }
   $rows = mysqli_query($this->mysqli, "SELECT * FROM `organizations` WHERE `id`='" . $this->mysqli->real_escape_string($id) . "'");
   if ($rows === false || $rows->num_rows > 1) {
     throw new HTTPBadRequestException($context);
   }
   if ($rows->num_rows == 0) {
     return null;
   }
   $row = mysqli_fetch_assoc($rows);
   $organization = array('id' => $row['id'], 'unique-id' => $row['unique-id'], 'name' => $row['name']);
   return $organization;
  }

  // @param $requestPathParts 
  // return json on success. null if no dispatch. exception on failure during dispatch.
  public function dispatch($context, $requestPathParts, $requestMethod, $arguments) {
    if ($requestMethod !== HTTPRequestMethod::Get) {
      return null;
    }
    // persons
    if (count($requestPathParts) == 1) {
      if ($requestPathParts[0] == 'organizations') {      
        $numberOfArguments = count($arguments);
        if ($numberOfArguments == 0) {
          return $this->findAll($context, 0, $this->getCount($context));
        } else if (isset($arguments['index']) && isset($arguments['count']) && $numberOfArguments == 2) {
          return $this->findAll($context, toInt($arguments['index']), toInt($arguments['count']));
        }
      } 
    } else if (count($requestPathParts) == 2) {
      if ($requestPathParts[0] == 'organizations') {      
        $numberOfArguments = count($arguments);
        if ($numberOfArguments == 0) {
          return $this->find($context, $requestPathParts[1]);
        }
      } 
    } else if (count($requestPathParts) == 3) {
      if ($requestPathParts[0] == 'organizations' && $requestPathParts[2] == 'tags') {      
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

