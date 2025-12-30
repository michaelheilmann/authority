<?php

// The model unifies inputs ($_GET, $_FILES, $_POST, API, etc.).
class ViewModel {

  // Get the pagination page from $_GET['page'] as an integer.
  // If $_GET['page'] is not defined or not a literal of the form ('+'|'-')['0'-'9']+['0'-'9']* then 1 is returned.
  // https://www.php.net/manual/en/language.types.numeric-strings.php
  function getActivePage() {
    if (!isset($_GET['page'])) {
      return 1;
    }  
    $pageString = $_GET['page'];
    $v = mb_str_split($pageString, 1, mb_internal_encoding());
    $start = 0;
    $end = count($v);
    $current = 0;
    if ($current == $end) {
      return 1;
    }
    if ('+' == $v[$current] || '-' == $v[$current]) {
      $current++;
    }
    if ($current == $end) {
      return 1;
    }
    if (!('0' <= $v[$current] && $v[$current] <= '9')) {
      return 1;
    }
    $current++;
    while (true) {
      if ($current == $end) {
        break;
      }
      if (!('0' <= $v[$current] && $v[$current] <= '9')) {
        return 1;
      }
      $current++;
    }
    $v = (int)$pageString;
    if ($v < 0) {
      return 1;
    }
    return $v;
  }

  // Get the category from $_GET['category'].
  // If $_GET['category'] is not defined or not one of the strings 'persons' or 'organizations', then 'persons' is returned.
  function getActiveCategory() {
    if (!isset($_GET['category'])) {
      return 'persons';
    } 
    $acceptedCategoryStrings = array('persons', 'organizations');
    $categoryString = $_GET['category'];
    if (!in_array($categoryString, $acceptedCategoryStrings, true)) {
      return 'persons';
    }
    return $categoryString;
  }
  
  // @brief Get the number of persons.
  // @return The number of persons.
  function getNumberOfPersons() {
    $p1 = AUTHORITY_API_URL . '/persons/';
    $p2 = array("index" => 0, "count" => 0);
    $curl = curl_init();
    $url = sprintf("%s?%s", $p1, http_build_query($p2));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($result === false || $httpStatusCode !== 200) {
      return null;  
    } else {
      $result = json_decode($result, true);
      return $result['numberOfElements'];
    }
  }
  
  // @brief Get the number of organizations.
  // @return The number of organizations on success. null on failure.
  function getNumberOfOrganizations() {
    $p1 = AUTHORITY_API_URL . '/organizations/';
    $p2 = array("index" => 0, "count" => 0);
    $curl = curl_init();
    $url = sprintf("%s?%s", $p1, http_build_query($p2));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($result === false || $httpStatusCode !== 200) {
      return null;
    } else {
      $result = json_decode($result, true);
      return $result['numberOfElements'];
    }
  }

  // @brief Get the persons [page, page * personsPerPage], page >= 0.
  // @param $page The page.
  // @param $personsPerPage The number of persons per page.
  // @return
  // { 'numberOfElements' : <the number of elements>, 'elements' : <the elements>} where each element is of type <person> on success.
  // null on failure.
  function getPersons($activePage, $personsPerPage) {
    $p1 = AUTHORITY_API_URL . '/persons/';
    $p2 = array("index" => ($activePage-1)*$personsPerPage, "count" => $personsPerPage);
    $curl = curl_init();
    $url = sprintf("%s?%s", $p1, http_build_query($p2));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($result === false || $httpStatusCode !== 200) {
      return null;
    } else {
      $result = json_decode($result, true);
      return $result;
    }
  }

  // @brief Get the tags of a person.
  // @param $id The ID of the person.
  // @return
  // List of tags of the person.
  // null on failure.
  function getPersonTags($id) {
    $p1 = AUTHORITY_API_URL . '/persons/' . $id . '/tags';
    $p2 = array();
    $curl = curl_init();
    $url = sprintf("%s?%s", $p1, http_build_query($p2));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($result === false || $httpStatusCode !== 200) {
      return null;
    } else {
      $result = json_decode($result, true);
      return $result;
    }
  }

  // @brief Get the organizations [page, page * organizationsPerPage], page >= 0.
  // @param $page The page.
  // @param $organizationsPerPage The number of persons per page.
  // @return
  // { 'numberOfElements' : <the number of elements>, 'elements' : <the elements>}  where each element is of type <organization> on success.
  // null on failure.
  function getOrganizations($activePage, $organizationsPerPage) {
    $p1 = AUTHORITY_API_URL . '/organizations/';
    $p2 = array("index" => ($activePage-1)*$organizationsPerPage, "count" => $organizationsPerPage);
    $curl = curl_init();
    $url = sprintf("%s?%s", $p1, http_build_query($p2));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($result === false || $httpStatusCode !== 200) {
      return null;
    } else {
      $result = json_decode($result, true);
      return $result;
    }
  }

};

?>

