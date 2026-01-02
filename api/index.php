<?php

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

/* Include the backend. */
require_once(__DIR__ . '/../backend/' . "include.php");
require_once(__DIR__ . '/../shared/' . "include.php");

/**
 * @brief Get the request method
 * @return The request method if it can be determined and is supported (HTTPRequestMethod)
 * @except we failed to determine the request method or the request method is not supported
 * 
 */
function getRequestMethod() : HTTPRequestMethod {
  switch (strtolower($_SERVER["REQUEST_METHOD"])) { 
    case 'get': return HTTPRequestMethod::Get;
    case 'patch': return HTTPRequestMethod::Patch;
    case 'post': return HTTPRequestMethod::Post;
    case 'put': return HTTPRequestMethod::Put;
    throw new Error('unable to determine request method');
  };
}

/**
 * @brief Get the request path
 * @return The request path if it can be determined (potentially empty array of strings).
 * @except we failed to determine the request path
 */
function getRequestPath() : array {
  $requestPath = explode('/', trim($_GET['request'], '/'));
  if (count($requestPath) > 0) {
    if (str_starts_with($requestPath[count($requestPath) - 1], '?')) {
     array_pop($requestPath);
    }
  }
  unset($_GET['request']);
  return $requestPath;
}

/**
 * @brief Get the request arguments
 * @return The request arguments if they can be determined (potentially empty associative array with strings as keys and values).
 * This array is always empty for non-get requests
 * @except null if we failed to determine the request arguments
 */
function getRequestArguments() : array {
  if ('get' === strtolower($_SERVER["REQUEST_METHOD"])) {  
    $get = array();
    foreach ($_GET as $key => $value) {
      $get[$key] = $value;
    }
    unset($get['request']);
    return $get;
  } else {
    return array();
  }
}

/**
 * @brief Get the request body
 * @return The request body if they can be determined (string).
 * null if there is no request body
 * @except null if we failed to determine the request arguments
 */
function getRequestBody() : string|null {
  $requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
  if ('put' === $requestMethod || 'patch' === $requestMethod || 'post' === $requestMethod) {
    return file_get_contents("php://input");    
  } else {
    return null;
  }
}


function handleRequest() {
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: GET");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  try {
    /* (1) Get the request method. */
    $requestMethod = getRequestMethod();
    if ($requestMethod === null) {
      /* Failed to determine request method or request method is not supported. */
      http_response_code(404);
      echo json_encode(array());
      return;
    }
    /* (2) Get the request path. */
    $requestPath = getRequestPath();
    if ($requestPath === null) {
      /* Failed to determine request path. */
      http_response_code(404);
      echo json_encode(array());
      return;
    }
    /* (3) Get the request arguments. */
    $requestArguments = getRequestArguments();
    if ($requestArguments === null) {
      /* Failed to determine request arguments. */
      http_response_code(404);
      echo json_encode(array());
      return;   
    }
    /* (4) Get the request body. */
    $requestBody = getRequestBody();
  } catch (Exception $e) {
    /* Failed to determine request arguments. */
    http_response_code(404);
    echo json_encode(array());
    return;       
  }

  $context = new HTTPRequestContext($requestMethod, $requestPath, $requestArguments, $requestBody);

  $handlers = array();
  $handlers[] = new PersonsHandler();
  $handlers[] = new OrganizationsHandler();
  try {
    foreach ($handlers as $handler) {
      $response = $handler->dispatch($context, $context->requestPath, $context->requestMethod, $context->requestArguments);
      if ($response !== null) {
        switch ($response->getStatusCode()) {
          case HTTPStatusCode::OK: {
            http_response_code(200);
            echo $response->getData()->getData();
          } break;
          case HTTPStatusCode::INTERNAL_ERROR: {
            http_response_code(500);
            echo $response->getData()->getData();
          } break;
          case HTTPStatusCode::BAD_REQUEST: {
            http_response_code(400);
            echo $response->getData()->getData();;
          } break;
          case HTTPStatusCode::UNAUTHORIZED: {
            http_response_code(401);
            echo $response->getData()->getData();
          } break;
          case HTTPStatusCode::NOT_FOUND: {
            http_response_code(404);
            echo $response->getData()->getData();
          } break;            
          default: {
            http_response_code(500);
            echo $response->getData()->getData();
          } break;
        }
        return;
      } else {
        continue;
      }
    }
    /* The requested resource was not found. */
    http_response_code(404);
    echo json_encode(array());
  } catch (Exception $e) {
    http_response_code(HTTPStatusCodes::INTERNAL_ERROR);
    echo json_encode(array());
  }
}

handleRequest();

?>
