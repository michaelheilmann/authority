<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
// header('Content-Type: application/json; charset=utf-8');

header('Content-Type: text/plain');

require_once(__DIR__ . '/../configuration.php');
require_once(__DIR__ . '/../backend/' . "db.php");
require_once(__DIR__ . '/../shared/' . "include.php");

// @return id of the file if it exists. 0 if the file does not exist. My_error on error.
function my_db_get_file_by_path($dbc, $path) {
  $query = "SELECT * FROM `files` WHERE `path`='" . $dbc->real_escape_string($path) . "'";
  $rows = mysqli_query($dbc, $query);
  if ($rows === false) {
    My_error("unable to get file", $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  if (!($rows instanceof mysqli_result)) {
    My_error("unable to get file", $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  $row = mysqli_fetch_assoc($rows);
  if ($row !== null) {
    return $row['id'];
  } else {
    return 0;
  }
}

/// @category db
/// @synopsis
/// Add/update a "file" entry.
/// @param $dbc
/// the database connection
/// @param $model
/// the model of the file. Has the form (path, status).
/// @return
/// @a true on success, @a false on failure
function my_db_write_file($dbc, $model) {
  $id = my_db_get_file_by_path($dbc, $model['path']);
  if (IsMyError($id)) {
    My_error("unable to get file id", $dbc->error);
    return $id;
  }
  if ($id == "0") {
    // Create the file entry.
    if (mysqli_query($dbc, "INSERT INTO `files` (`id`, `path`, `status`) VALUES (NULL, '" . $dbc->real_escape_string($model['path']) . "', '" . $dbc->real_escape_string($model['status']) . "')") === false) {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert file record (" . $model['path'] . ", " . $model['status'] . ")", $dbc->errno);
      return new MyError();
    }
    $id = mysqli_insert_id($dbc);
    if (0 === $id) {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert/update file record record", $dbc->errno);
      return new MyError(__FILE__, __LINE__);
    }
  } else {
    // Update the file entry.
    if (mysqli_query($dbc, "UPDATE `files` SET `path`='" . $dbc->real_escape_string($model['path']) . "', `status`='" . $dbc->real_escape_string($model['status']) . "' WHERE `id`='" . $dbc->real_escape_string($id) . "'") === false) {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert file record", $dbc->errno);
      return new MyError(__FILE__, __LINE__);
    }
  }
  return $id;
}



/// @return id of the tag if it exists. 0 if the tag does not exist. MyError on error.
function my_db_get_tag_by_name($dbc, $name) {
  $rows = mysqli_query($dbc, "SELECT * FROM `tags` WHERE `name`='" .$dbc->real_escape_string($name) . "' LIMIT 1");
  if ($rows === false) {
    My_error("unable to get tag", $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  $row = mysqli_fetch_assoc($rows);
  if ($row !== null) {
    $id = $row['id'];
    return $id;
  } else {
    return '0';
  }
}

/// @category db
/// @synopsis
/// Add/update a "tag" entry.
/// @param $dbc
/// the database connection
/// @param model
/// the model of the tag. Has the form (name)
/// @return
/// the id of the added/updated "tag" entry on success. false on failure.
function my_db_add_tag($dbc, $model) {
  $id = my_db_get_tag_by_name($dbc, $model['name']);
  if (IsMyError($id)) {
    My_error("unable to get tag record", $dbc->errno);
    return $id;
  }
  if ($id != "0") {
    return $id;
  }
  if (mysqli_query($dbc, "INSERT INTO `tags` (`id`, `name`) VALUES (NULL, '" . $dbc->real_escape_string($model['name']) . "')") === false) {
    My_error("unable to insert tag record for tag '" . $model['name'] . "'", $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  $id = mysqli_insert_id($dbc);
  if ($id == "0") {
    My_error("unable to insert tag record for tag '" . $model['name'] . "'", $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  return $id;
}

/**
 * @brief Get a node for an unique id.
 * @param $dbc The database connection.
 * @param $unique_id The unique ID.
 * @return The database ID of the node (not the ID of the person or the organization) on success. A MyError object on failure.
 */
function my_db_get_node_by_unique_id($dbc, $unique_id) {
  $rows = mysqli_query($dbc, "SELECT `nodes`.`id` as `id` FROM `nodes`"
                             . " " . "WHERE `nodes`.`unique-id`='" . $dbc->real_escape_string($unique_id) . "'");
  if ($rows === false) {
    My_error("unable to get node", $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  $row = mysqli_fetch_assoc($rows);
  if ($row !== null) {
    $id = $row['id'];
    return $id;
  } else {
    return '0';
  }
}

/**
 * @brief Get a node for an unique id.
 * @param $dbc The database connection.
 * @param $unique_id The unique ID.
 * @return The database ID of the person (not the ID of the node) on success. A MyError object on failure.
 */
function my_db_get_person_by_unique_id($dbc, $unique_id) {
  $rows = mysqli_query($dbc, "SELECT `persons`.`id` as `id` FROM `persons` INNER JOIN `nodes` ON `persons`.`node-id` = `nodes`.`id`"
                             . " " . "WHERE `nodes`.`unique-id`='" . $dbc->real_escape_string($unique_id) . "'");
  if ($rows === false) {
    My_error("unable to get node", $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  $row = mysqli_fetch_assoc($rows);
  if ($row !== null) {
    $id = $row['id'];
    return $id;
  } else {
    return '0';
  }
}

/// @category db
/// @synopsis
/// Set the tags of a node. node and tags must exist.
/// @param $dbc
/// the database connection
/// @param $nodeId
/// The ID of the node.
/// @param $tagIds
/// The IDs of the tags.
/// @return MyError | boolean @a true on success. MyError object on failure
function My_Db_setNodeTags($dbc, $nodeId, $tagIds) {
  if (mysqli_query($dbc, "DELETE FROM `tags_nodes` WHERE `node-id`='" . $dbc->real_escape_string($nodeId) . "'") === false) {
    My_error("unable to update tags for node.", "error code: " . $dbc->errno);
    return new MyError(__FILE__, __LINE__);
  }
  foreach ($tagIds as $tagId) {
    if (mysqli_query($dbc, "INSERT INTO `tags_nodes` (`id`, `tag-id`, `node-id`) VALUES(NULL, '" . $dbc->real_escape_string($tagId) . "', '" . $dbc->real_escape_string($nodeId) . "')") === false) {
      My_error("unable to update tags for node.", "error code: " . $dbc->errno);
      return new MyError(__FILE__, __LINE__);
    }
  }
  return true;
}

/// @category db
/// @synopsis
/// Add/update a "person" entry.
/// @param $dbc
/// the database connection
/// @param model
/// the model of the tag. Has the form (unique-id, prename, surname)
/// @return MyError | int The ID of the person. MyError object on failure
function My_Db_writePerson($dbc, $fileId, $model) {
  $nodeID = '0';
  $personID = '0';

  $nodeID = my_db_get_node_by_unique_id($dbc, $model['unique-id']);
  if (IsMyError($nodeID)) {
    return $nodeID;
  }
  $personID = my_db_get_person_by_unique_id($dbc, $model['unique-id']);
  if (IsMyError($personID)) {
    return $personID;
  }

  if ($personID != '0') {
    if (mysqli_query($dbc, "UPDATE `persons` SET `file-id`='" . $dbc->real_escape_string($fileId)
                                                              . "', `prename`='" . $dbc->real_escape_string($model['prename'])
                                                              . "', `surname`='" . $dbc->real_escape_string($model['surname'])
                                                              . "' WHERE `id`='" . $dbc->real_escape_string($personID) . "'") === false) {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert person with unique id '" . $model['unique-id'] . "'", $dbc->errno, mysqli_error($dbc));
      return new MyError(__FILE__, __LINE__);
    }
  } else {
    if (mysqli_query($dbc, "INSERT INTO `nodes` (`id`, `unique-id`)" .
                           "VALUES(NULL, '" . $dbc->real_escape_string($model['unique-id']) . "')") === false) {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert person with unique id '" . $model['unique-id'] . "'", $dbc->errno, mysqli_error($dbc));
      return new MyError(__FILE__, __LINE__);
    }
    $nodeID = mysqli_insert_id($dbc); // obtain the ID
    if ($nodeID == "0") {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert person with unique id '" . $model['unique-id'] . "'", $dbc->errno, mysqli_error($dbc));
      return new MyError(__FILE__, __LINE__);
    }
    if (mysqli_query($dbc, "INSERT INTO `persons` (`id`, `node-id`, `file-id`, `prename`, `surname`)" .
                           "VALUES(NULL, '" . $dbc->real_escape_string($nodeID)
                                            . "', '" . $dbc->real_escape_string($fileId)
                                            . "', '" . $dbc->real_escape_string($model['prename'])
                                            . "', '" . $dbc->real_escape_string($model['surname']) . "')") === false) {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert person with unique id '" . $model['unique-id'] . "' and node id `" . $nodeID . "`", $dbc->errno, mysqli_error($dbc));
      return new MyError(__FILE__, __LINE__);
    }
    $personID = mysqli_insert_id($dbc);
    if ($personID == "0") {
      My_error(__FILE__ . ":" . __LINE__ . ": unable to insert person with unique id '" . $model['unique-id'] . "'", $dbc->errno, mysqli_error($dbc));
      return new MyError(__FILE__, __LINE__);
    }
  }
  // (2) update "tags" table
  $tag_ids = array();
  foreach ($model['tags'] as $tag) {
    $tag_ids[] = my_db_add_tag($dbc, array("name" => $tag));
  }
  // (3) update "tag"/node" association table table
  My_Db_setNodeTags($dbc, $nodeID, $tag_ids);
  // (4) done
  return $personID;
}

function my_read_person_from_file($file) {
  $text = file_get_contents($file);
  if ($text === false) {
    return false;
  }
  $data = json_decode($text, true);
  if ($text === null) {
    return false;
  }
  if (!isset($data['id'])) {
    return false;  
  }
  if (!is_string($data['id'])) {
    return false;
  }
  if (!isset($data['prename'])) {
    return false;  
  }
  if (!is_string($data['prename'])) {
    return false;
  }
  if (!isset($data['surname'])) {
    return false;
  }
  if (!is_string($data['surname'])) {
    return false;
  }
  if (!isset($data['tags'])) {
    return false;
  }
  if (!is_array($data['tags'])) {
    return false;
  }
  return array('unique-id' => $data['id'],
               'prename' => $data['prename'],
               'surname' => $data['surname'],
               'tags' => $data['tags']);
}

// open connection
$mysqli = new mysqli(AUTHORITY_DB_HOST, AUTHORITY_DB_USER_NAME, AUTHORITY_DB_USER_PASSWORD, AUTHORITY_DB_NAME, AUTHORITY_DB_PORT, AUTHORITY_DB_SOCKET);
if ($mysqli->connect_errno) {
  die("connection failed: " . $mysqli->connect_error);
}

$dirPath = __DIR__ . '/../' . "data";
if ($handle = opendir($dirPath)) {
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != ".." && is_file($dirPath . '/' . $entry)) {
      $ext = pathinfo($entry, PATHINFO_EXTENSION);
      if ($ext === 'json') {
        my_db_write_file($mysqli, array("path" => $dirPath . '/' . $entry, "status" => "unprocessed"));
      }
    }
  }
  closedir($handle);
}

function on_parse($db) {
  $result = mysqli_query($db, "SELECT * FROM `files` WHERE `status`='unprocessed' LIMIT 100");
  if ($result === false) {
    return false;   
  }
  $files = array();
  while ($sqlFiles = mysqli_fetch_assoc($result)) {
    $files[] = array("id" => $sqlFiles['id'], "path" => $sqlFiles["path"]);
  }
  mysqli_free_result($result);  
  foreach ($files as $file) {
    if (mysqli_query($db, "UPDATE `files` SET `status`='processing' WHERE `id`='" . $file['id'] . "'") === false) {
      My_error("failed to update file status to 'processing' for file '" . $file['id'] . "''", mysqli_error($db));
      return false;
    }
    $contents = my_read_person_from_file($file['path']);
    if ($contents === false) {
      My_error("failed to read person from file '" . $file['path'] . "'", null);
      return false;
    }
    if (My_Db_writePerson($db, $file['id'], $contents) === false) {
      My_error("unable to insert person", mysqli_error($db));
      return false;
    }
    if (mysqli_query($db, "UPDATE `files` SET `status`='processing-success' WHERE `id`='" . $file['id'] . "'") === false) {
      My_error("unable to insert person", mysqli_error($db));
      return false;
    }
  }
  return true;
}

on_parse($mysqli);

$mysqli->close();

?>
