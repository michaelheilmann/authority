<?php

enum HTTPStatusCode {
  /* 200 / ok */
  case OK;
  /* 500 / internal server error */
  case INTERNAL_ERROR;
  /* 400 / bad request */
  case BAD_REQUEST;
  /* 401 / unauthorized */
  case UNAUTHORIZED;
  /* 404 / not found */
  case NOT_FOUND;
};

?>
