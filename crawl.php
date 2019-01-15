<?php
include "config.php"; // database connection
include "classes/DomDocumentParser.php"; // DomDocumentParser class

$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();

// insert link to database
function insertLink($url, $title, $description, $keywords)
{
    global $con;

    $query = $con->prepare("INSERT INTO sites(url, title, description, keywords)
                            VALUES(:url, :title, :description, :keywords)");

    $query->bindParam(":url", $url);
    $query->bindParam(":title", $title);
    $query->bindParam(":description", $description);
    $query->bindParam(":keywords", $keywords);

    return $query->execute();

}

// insert image to database
function insertImage($url, $src, $alt, $title)
{
    global $con;

    $query = $con->prepare("INSERT INTO images(siteUrl, imageUrl, alt, title)
                            VALUES(:siteUrl, :imageUrl, :alt, :title)");

    $query->bindParam(":siteUrl", $url);
    $query->bindParam(":imageUrl", $src);
    $query->bindParam(":alt", $alt);
    $query->bindParam(":title", $title);

    $query->execute();

}

// check if link url already exists in database, returns true if link already exists
function linkExists($url)
{
    global $con;

    $query = $con->prepare("SELECT * FROM sites WHERE url = :url");

    $query->bindParam(":url", $url);
    $query->execute();

    return $query->rowCount() != 0;

}

// convert relative url to absolute url
function createLink($src, $url)
{

    $scheme = parse_url($url)["scheme"]; // http
    $host = parse_url($url)["host"]; // www.host.com

    if (substr($src, 0, 2) == "//") {
        $src = $scheme . ":" . $src;
    } else if (substr($src, 0, 1) == "/") {
        $src = $scheme . "://" . $host . $src;
    } else if (substr($src, 0, 2) == "./") {
        $src = $scheme . "://" . $host . dirname(parse_url($url)["path"]) . substr($src, 1);
    } else if (substr($src, 0, 3) == "../") {
        $src = $scheme . "://" . $host . "/" . $src;
    } else if (substr($src, 0, 5) != "https" && substr($src, 0, 4) != "http") {
        $src = $scheme . "://" . $host . "/" . $src;
    }

    return $src;
}

// get details from HTML tags
function getDetails($url)
{

    $parser = new DomDocumentParser($url);

    // get the title tag element and store it in an array
    $titleArray = $parser->getTitleTags();

    // if there are no title tags in $titleArray or the first element is empty, return from getDetails
    if (sizeof($titleArray) == 0 || $titleArray->item(0) == null) {
        return;
    }

    // get the text value of the first item in $titleArray
    $title = $titleArray->item(0)->nodeValue;

    // replace any new lines in the title with empty strings
    $title = str_replace("\n", "", $title);

    // if the title is an empty string, return from getDetails
    if ($title == "") {
        return;
    }

    $description = "";
    $keywords = "";

    // get meta tags and store them in an array
    $metasArray = $parser->getMetaTags();

    // iterate through $metasArray and get the context of the name attribute if it is equal to description or keywords
    foreach ($metasArray as $meta) {

        if ($meta->getAttribute("name") == "description") {
            $description = $meta->getAttribute("content");
        }

        if ($meta->getAttribute("name") == "keywords") {
            $keywords = $meta->getAttribute("content");
        }

    }

    // replace any new lines with empty strings
    $description = str_replace("\n", "", $description);
    $keywords = str_replace("\n", "", $keywords);

    // if the link url does not exist in the database, insert the url, title, description and keywords to the sites table
    if (linkExists($url)) {
        echo "$url already exists<br>";
    } else if (insertLink($url, $title, $description, $keywords)) {
        echo "SUCCESS: $url";
    } else {
        echo "ERROR: Failed to insert $url<br>";
    }

    // get image tags from the current site
    $imageArray = $parser->getImageTags();

    //iterate through image tags and get the src, alt and title attributes
    foreach ($imageArray as $image) {
        $src = $image->getAttribute("src");
        $alt = $image->getAttribute("alt");
        $title = $image->getAttribute("title");

        // if there is no title or alt attribute, continue the iteration
        if (!$title && !$alt) {
            continue;
        }

        // create absolute links out of the current src
        $src = createLink($src, $url);

        global $alreadyFoundImages;

        // if the image src is not already in the alreadyFoundImages array then push it on to the array
        if (!in_array($src, $alreadyFoundImages)) {
            $alreadyFoundImages[] = $src;

            // insert image into the images table of the database
            insertImage($url, $src, $alt, $title);
        }

    }

}

function followLinks($url)
{

    global $alreadyCrawled;
    global $crawling;

    $parser = new DomDocumentParser($url);

    // get all a tags
    $linkList = $parser->getLinks();

    // iterate through linkList array
    foreach ($linkList as $link) {
        $href = $link->getAttribute("href");

        // if the href is a # or javascript, ignore it and continue iteration
        if (strpos($href, "#") !== false) {
            continue;
        } else if (substr($href, 0, 11) == "javascript:") {
            continue;
        }

        // turn relative href into absolute href
        $href = createLink($href, $url);

        // if the href is not in alreadyCrawled then add it to alreadyCrawled and crawling arrays
        if (!in_array($href, $alreadyCrawled)) {
            $alreadyCrawled[] = $href;
            $crawling[] = $href;

            getDetails($href);

        }

    }

    // remove first item from the crawling array
    array_shift($crawling);

    // iterate through sites in the crawling array, recursively call followLinks on the current site
    foreach ($crawling as $site) {
        followLinks($site);
    }

}

$startUrl = "https://www.google.com/search?source=hp&ei=gNI7XK_cFMW-0PEPjf-K8A4&q=dogs&btnK=Google+Search&oq=dogs&gs_l=psy-ab.3..35i39j0j0i131j0j0i131j0j0i131j0l3.858.1148..1229...0.0..0.78.273.4......0....1..gws-wiz.....0.a4Kj6CRY7YY";
followLinks($startUrl);
