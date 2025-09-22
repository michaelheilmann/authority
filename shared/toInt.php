<?php

/**
 * @brief Get if a value can be converted to an integer.
 * @param $x The value.
 * @return The integer or null.
 */
function toInt($x) {
  if (is_int($x)) {
    return $x;
  } else if (is_string($x)) {
    $i = 0;
    if (strlen($x) == $i) {
      return false;
    }
    if ($x[$i] == '-' || $x[$i] == '+') {
      $i++;
    }
    if (strlen($x) == $i) {
      return null;
    }
    if (!ctype_digit($x[$i])) {
      return null;
    }
    do {
      $i++;
      if (strlen($x) == $i) {
        break;
      }
    } while (ctype_digit($x[$i]));
    return (int)$x;
  } else {
    return null;
  }
}

?>

