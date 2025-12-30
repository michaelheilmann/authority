<?php

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

/* Include the backend. */
require_once(__DIR__ . '/../backend/' . "include.php");
require_once(__DIR__ . '/../shared/' . "include.php");

/** @brief Helper function to build the request method for the HTTPRequestContext. */
function buildRequestMethod() : HTTPRequestMethod|null {
  switch (strtolower($_SERVER["REQUEST_METHOD"])) { 
    case 'get': return HTTPRequestMethod::Get;
    case 'patch': return HTTPRequestMethod::Patch;
    case 'post': return HTTPRequestMethod::Post;
    case 'put': return HTTPRequestMethod::Put;
    return null;
  };
}

/** @brief Helper function to build the request for the HTTPRequestContext. */
function buildRequestPath() {
  $requestPath = explode('/', trim($_GET['request'], '/'));
  if (count($requestPath) > 0) {
    if (str_starts_with($requestPath[count($requestPath) - 1], '?')) {
     array_pop($requestPath);
    }
  }
  unset($_GET['request']);
  return $requestPath;
}

/** @brief Helper function to build the request arguments for the HTTPRequestContext. */
function buildRequestArguments() {
  $get = array();
  foreach ($_GET as $key => $value) {
    $get[$key] = $value;
  }
  unset($get['request']);
  return $get;
}

function handleRequest() {
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: GET");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  $context = new HTTPRequestContext();
  // (1) Get the request method.
  $context->requestMethod = buildRequestMethod();
  // (2) get the request path.
  $context->requestPath = buildRequestPath();
  // (3) get the arguments
  $context->requestArguments = buildRequestArguments();

  $handlers = array();
  $handlers[] = new PersonsHandler();
  $handlers[] = new OrganizationsHandler();
  try {
    foreach ($handlers as $handler) {
      $json = $handler->dispatch($context, $context->requestPath, $context->requestMethod, $context->requestArguments);
      if ($json !== null) {
        echo json_encode($json, JSON_UNESCAPED_SLASHES);
        return;
      } else {
        continue;
      }
    }
    http_response_code(HTTPStatusCodes::BAD_REQUEST);
    echo json_encode(array());
  } catch (HTTPInternalErrorException $e) {
    http_response_code(HTTPStatusCodes::OK);
    echo json_encode(array());
    /*echo json_encode(array( "requestMethod" => $context->requestMethod, "requestPath" => $context->requestPath, "error" => "internal error" ), JSON_UNESCAPED_SLASHES);*/
  } catch (HTTPBadRequestException $e) {
    http_response_code(HTTPStatusCodes::BAD_REQUEST);
    echo json_encode(array());
    /*echo json_encode(array( "requestMethod" => $context->requestMethod, "requestPath" => $context->requestPath, "error" => "bad request" ), JSON_UNESCAPED_SLASHES);*/
  } catch (HTTPException $e) {
    http_response_code(HTTPStatusCodes::INTERNAL_ERROR);
    echo json_encode(array());
    /*echo json_encode(array( "requestMethod" => $context->requestMethod, "requestPath" => $context->requestPath, "error" => "undetermined" ), JSON_UNESCAPED_SLASHES);*/
  } catch (Exception $e) {
    http_response_code(HTTPStatusCodes::INTERNAL_ERROR);
    echo json_encode(array());
    /*echo json_encode(array( "requestMethod" => $context->requestMethod, "requestPath" => $context->requestPath, "error" => "undetermined" ), JSON_UNESCAPED_SLASHES);*/
  }
}

handleRequest();

?>
