<?php

enum HTTPStatusCodes {
  /* 200 / ok */
  case OK;
  /* 500 / internal error */
  case INTERNAL_ERROR;
  /* 400 / bad request */
  case BAD_REQUEST;
  /* 401 / unauthorized */
  case UNAUTHORIZED;
};

?>
