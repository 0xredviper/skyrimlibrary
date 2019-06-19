<?php

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

if ( !array_key_exists("command", $_GET) ) {
  exit("{'error':'The parameter -command- is missing.'}");
}

$command = $_GET['command'];

preg_match_all("/([a-zA-Z0-9]|[-]|[_])/", $command, $matches);

if ( count($matches[0]) < strlen($command) ) {
  exit("{'error':'The query contains invalid characters.'}");
}

if ( $command == "listbooks" ) {

  $jsonText = "{\"bookslist\":[";

  $handle = fopen("index.html", "r");
  if ($handle) {
      $isLink = false;
      while (($line = fgets($handle)) !== false) {
          // process the line read.
          if ( stripos($line, "href") > 0 && stripos($line, "style.css") == false ) {
            $isLink = true;
            $strLink = substr($line, stripos($line, "href=\"")+6);
            $strLink = substr($strLink, 0, stripos($strLink, "\"")-5);
            $jsonText .= "{\"link\":\"" . trim($strLink) . "\",";
            continue;
          }

          if ($isLink) {
            $line = str_ireplace("\"", "\\\"", $line);
            if ( array_key_exists("base64", $_GET) && $_GET['base64'] == "on" ) {
              $jsonText .= "\"title\":\"" . base64_encode(trim($line)) . "\"},";
            } else {
              $jsonText .= "\"title\":\"" . trim($line) . "\"},";
            }
            $isLink = false;
            continue;
          }
      }

      fclose($handle);

      if ( substr($jsonText, strlen($jsonText)-1) == "," ) {
        $jsonText = substr($jsonText, 0, strlen($jsonText)-1);
      }

      $jsonText .= "]}";

      echo $jsonText;

  } else {
      // error opening the file.
  }

} elseif ( $command == "getbook" ) {

  if ( !array_key_exists("link", $_GET) ) {
    exit("{\"error\":\"The parameter -link- is missing.\"}");
  }

  $link = $_GET['link'];

  preg_match_all("/([a-zA-Z0-9]|[-]|[_])/", $link, $matches);

  if ( count($matches[0]) < strlen($link) ) {
    exit("{\"error\":\"The query contains invalid characters.\"}");
  }

  $jsonText = "{\"book\":{";

  $fileName = $link . ".html";

  $handle = fopen($fileName, "r");
  if ($handle) {
    $content = fread($handle, filesize($fileName));
    $jsonTitle = substr($content, stripos($content, "<title>")+7);
    $jsonTitle = substr($jsonTitle, 0, stripos($jsonTitle, "</title>"));

    $jsonText .= "\"title\":\"$jsonTitle\",";

    rewind($handle);

    $fileContent = "";
    $nextLineIsAuthor = false;
    $isTheAuthorPassed = false;
    $isBodyStarted = false;
    while (($line = fgets($handle)) !== false) {

      if (startsWith(trim($line), "<body>") ) {
        $isBodyStarted = true;
      }

      if (!$isBodyStarted) {
        continue;
      }

      //Check if the line is an image tag, then strip the base64 content
      if ( stripos($line, "<img") ) {
        $line = substr($line, stripos($line, "src=\"")+5);
        $line = substr($line, 0, stripos($line, "\""));
      }

      $line = strip_tags(trim($line));

      $line = str_ireplace("\"", "\\\"", $line);

      $fileContent .= $line . "\\n";

      // If this is marked as the line with the author, it creates the author JSON variable
      if ($nextLineIsAuthor && !$isTheAuthorPassed && $line != "") {
        $jsonText .= "\"author\":\"$line\",";
        $nextLineIsAuthor = false;
        $isTheAuthorPassed = true;
      }

      // If there is the word "di" then probably the next line is the author
      if (trim($line) === "di") {
        $nextLineIsAuthor = true;
      }

    }

  }

  fclose($handle);

  if ( array_key_exists("base64", $_GET) && $_GET['base64'] == "on" ) {
    $fileContent = base64_encode($fileContent);
  }

  $jsonText .= "\"content\":\"$fileContent\"}}";

  echo $jsonText;

}

?>
