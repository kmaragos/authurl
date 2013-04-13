<?php
  // Read request parameters from file
  require_once('config.php');


  // Step 1
  // Post login credentials to login page
  // and receive cookie with 'uid' and 'pass'.

  // Initiliaze string to receive headers
  $headers_string = '';

  $curl_params['url'] = $login_url;
  $ch = curl_it($curl_params, true);
  $login_result = curl_exec($ch);
  // $login_result contains the page returned after login
  // in the cases I have tested, it is the page with login form

  if ($error = curl_error($ch)) {
    echo "<p>Error: {$error}</p>\n"; //debug
  }
  curl_close($ch);

  $headers = http_parse_headers($headers_string);
  $auth_info = mine_auth_info($headers);


  // Step 2
  // Get desired page by sending 'uid' and 'pass'
  // as cookie in request headers

  // Initiliaze string to receive headers
  $headers_string = '';

  $curl_params['url'] = $browse_url;
  $curl_params['data'] = "uid={$auth_info['uid']}; pass={$auth_info['pass']}";
  $ch = curl_it($curl_params);
  $page_result = curl_exec($ch);
  // $page_result contains the HTML of the target page
  if ($error = curl_error($ch)) {
    echo "<p>Error: {$error}</p>\n"; //debug
  }
  curl_close($ch);

  // writes returned HTTP headers in global string
  // called by cURL
  function read_header($ch, $string) {
    $GLOBALS['headers_string'] .= $string;
    $length = strlen($string);

    return $length;
  }

  // returns body length
  // called by cURL
  function read_body($ch, $string) {
    $length = strlen($string);

    return $length;
  }

  // Parse header and return data in an array
  function http_parse_headers($header) {
    $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
    $fields_array = parse_header_fields($fields);

    return $fields_array;
  }

  // Parse header fields
  function parse_header_fields($fields) {
    $fields_array = array();

    foreach( $fields as $field ) {
      if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
        $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
        if (isset($fields_array[$match[1]])) {
          $fields_array[$match[1]] = array($fields_array[$match[1]], $match[2]);
        } else {
          $fields_array[$match[1]] = trim($match[2]);
        }
      }
    }

    return $fields_array;
  }

  // Prepare cURL handle
  function curl_it($params, $post = false) {
    $ch = curl_init($params['host']);

    curl_setopt($ch, CURLOPT_URL, $params['url']);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'read_body');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($post) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $params['data']);
      curl_setopt($ch, CURLOPT_POST, true);
    } else {
      curl_setopt($ch, CURLOPT_COOKIE, $params['data']);
    }

    return $ch;
  }

  // Extract 'uid' and 'pass' values from raw 'Set-Cookie' header data
  function mine_auth_info($headers) {
    $cookie = $headers['Set-Cookie'];
    $uid_cookie = explode(';', $cookie[0]);
    $uid = explode('=', $uid_cookie[0]);
    $info['uid'] = $uid[1];
    $pass_cookie = explode(';', $cookie[1]);
    $pass = explode('=', $pass_cookie[0]);
    $info['pass'] = $pass[1];

    return $info;
  }
