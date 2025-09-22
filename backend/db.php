<?php

require_once(__DIR__ . '/../' . "configuration.php");
require_once(__DIR__ . '/../shared/' . "include.php");

/**
 * @brief Get the number of tags
 *
 * @param $dbc the database connection
 *
 * @return My_Error | int the number of tags on success. My_Error object on failure
 */
function My_Db_getNumberOfTags($dbc) {
  $rows = mysqli_query($dbc, "SELECT COUNT(*) as `numberOfTags` FROM `tags`");
  if ($rows === false) {
    return new My_Error(1, "unable to determine number of tags");   
  }
  $row = mysqli_fetch_assoc($rows);
  return $row['numberOfTags'];
}

/**
 * @brief Get the number of persons
 *
 * @param $dbc the database connection
 *
 * @return My_Error | int the number of persons on success. My_Error object on failure
 */
function My_Db_getNumberOfPersons($dbc) {
  $rows = mysqli_query($dbc, "SELECT COUNT(*) as `numberOfPersons` FROM `persons`");
  if ($rows === false) {
    return new My_Error(1, "unable to determine number of persons");   
  }
  $row = mysqli_fetch_assoc($rows);
  return $row['numberOfPersons'];
}

/**
 * @brief Get up to $count persons starting at the person with the zero-based index $start.
 * For this selection, the persons are sorted first by their surname and second by their prename.
 * @param $dbc Th database connection
 * @param $start The zero-based index of the person.
 * @param $count The number of persons.
 * @return My_Error | array{id:int,prename:string,surname:string}
 */
function My_Db_getPersons($dbc, $start, $count) {
  $rows = mysqli_query($dbc, "SELECT * FROM `persons` ORDER BY `surname`, `prename` LIMIT " . $start . ", " . $count);
  if ($rows === false) {
    return new My_Error(1, "unable to get persons");   
  }
  $persons = array();
  while ($row = mysqli_fetch_assoc($rows)) {
    $person = array('id' => $row['id'], 'prename' => $row['prename'], 'surname' => $row['surname']);
    $persons[] = $person;
  }
  return $persons;
}

/*
 * @param $dbc Th database connection
 * @return My_Error | array{id:int,person_id:int,tag_id:int,name:string}
 */
function My_Db_getTagsOfPerson($dbc, $personId) {
  $rows = mysqli_query($dbc, "SELECT * FROM `tags_persons` as lhs LEFT JOIN `tags` as rhs on lhs.tag_id = rhs.id WHERE lhs.person_id='" . $personId . "'");
  if ($rows === false) {
    return new My_Error(1, "unable to get tags " . mysqli_error($dbc));
  }
  $tags = array();
  while ($row = mysqli_fetch_assoc($rows)) {
    $tag = array('id' => $row['id'], 'person_id' => $row['person_id'], 'tag_id' => $row['tag_id'], 'name' => $row['name']);
    $tags[] = $tag;
  }
  return $tags;
}

?>

