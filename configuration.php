<?php

/*
 * The host of the database.
 * @default The default value for a typical XAMPP development system is usually `"localhost"`.
 */
define("AUTHORITY_DB_HOST", "localhost");

/* 
 * The name of the database.
 * @default The default value for a typical XAMPP development system is usually `"authority-fictional"`.
 */
define("AUTHORITY_DB_NAME", "authority-fictional");

/*
 * @brief The name of the user of the database.
 * @default The default value for a typical XAMPP development system is usually `"root"`.
 */
define("AUTHORITY_DB_USER_NAME", "root");

/*
 * @brief The password of the user of the database.
 * @default The default value for a typical XAMPP development system is usually `""`.
 */
define("AUTHORITY_DB_USER_PASSWORD", "");

/*
 * @brief The port number of the database. 
 * @default The default value for a typical XAMPP development system is usually `NULL`.
 */
define("AUTHORITY_DB_PORT", NULL);

/*
 * @brief The socket of the database.
 * @default The default value for a typical XAMPP development system is usually `NULL`.
 */
define("AUTHORITY_DB_SOCKET", NULL);

/*
 * @brief Prevents certain diagnoistics to be emitted for privacy reasons.
 * @default The default value for any installation is `true`.
 */
define("AUTHORITY_DB_PRIVACY_SHIELD", true);

/*
 * @brief URL of the REST API (with trailing slash).
 * @default The default value for a typical XAMPP development system is usually `"http://localhost/"`.
 */
define("AUTHORITY_API_URL", "http://localhost/api/");

/*
 * @brief URL of the site (with trailing slash).
 * @default The default value for a typical XAMPP development system is usually `"http://localhost/"`.
 */
define("AUTHORITY_WS_URL", "http://localhost/");

/**
 * @brief Copyright notice for the frontend.
 */
define("AUTHORITY_WS_COPYRIGHT", "© 2019-2025 Michael Heilmann");

/**
 * @brief Author for the frontend.
 */
define("AUTHORITY_WS_AUTHOR", "Michael Heilmann");

/**
 * @brief Title of the website.
 */
define("AUTHORITY_WS_TITLE", "Authority");

/**
 * @brief Subtitle of the website.
 */
define("AUTHORITY_WS_SUBTITLE", "Fictional Demo Edition");

/**
 * @brief Keywords.
 * This is displayed in `<meta name="keywords" content="...">`.
 * @warning Do not use comma in a keyword.
 */
define("AUTHORITY_WS_KEYWORDS", array("demo", "demonstration", "fictional data set", "intelligence system"));

/**
 * @brief Description.
 * This is displayed in `<meta name="description" content="...">`.
 */
define("AUTHORITY_WS_DESCRIPTION", "demo of Authority, an intelligence system, using fictional data sets for demonstration purposes");

?>
