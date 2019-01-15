<?php

include "config.php";
include "classes/SiteResultsProvider.php";
include "classes/ImageResultsProvider.php";

if (isset($_GET["term"])) {
    $term = $_GET["term"];
} else {
    exit("You must enter a search term");
}

$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
$page = isset($_GET["page"]) ? $_GET["page"] : 1;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="description" content="Search the web for sites and images.">
  <meta name="keywords" content="search engine, foofle, search, google clone">
  <meta name="author" content="Tony Pettigrew">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Welcome to Foofle</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.css" />
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
</head>
<body>

  <div class="wrapper">

   <div class="header">

    <div class="header-content">

      <div class="logo-container">
        <a href="index.php">
          <img src="assets/img/logo.png" alt="logo">
        </a>
      </div>

      <div class="search-container">

        <form action="search.php" method="GET">

          <div class="search-bar-container">
            <input type="hidden" name="type" value="<?php echo $type ?>">
            <input name="term" type="text" class="search-box" value="<?php echo $term ?>">
            <button class="search-button">
              <img src="assets/img/icons/search.png" alt="search icon">
            </button>
          </div>

        </form>

      </div>

    </div>

    <div class="tabs-container">

      <ul class="tab-list">

        <li class="<?php echo $type == 'sites' ? 'active' : '' ?>">
          <a href='<?php echo "search.php?term=$term&type=sites"; ?>'>Sites</a>
        </li>
        <li class="<?php echo $type == 'images' ? 'active' : '' ?>">
          <a href='<?php echo "search.php?term=$term&type=images"; ?>'>Images</a>
        </li>

      </ul>

    </div>

   </div>

   <div class="main-results-section">
<?php
if ($type == "sites") {

    $resultsProvider = new SiteResultsProvider($con);

    $pageSize = 20;
} else {

    $resultsProvider = new ImageRestultsProvider($con);

    $pageSize = 30;

}

$numResults = $resultsProvider->getNumResults($term);

echo "<p class='results-count'>$numResults results found</p>";

echo $resultsProvider->getResultsHtml($page, $pageSize, $term);
?>
   </div>

   <div class="pagination-container">

    <div class="page-buttons">

      <div class="page-number-container">
        <img src="assets/img/page-start.png">
      </div>

<?php
$pagesToShow = 10;
$numPages = ceil($numResults / $pageSize);
$pagesLeft = min($pagesToShow, $numPages);

$currentPage = $page - floor($pagesToShow / 2);

if ($currentPage < 1) {
    $currentPage = 1;
}

if ($currentPage + $pagesLeft > $numPages + 1) {
    $currentPage = $numPages + 1 - $pagesLeft;
}

while ($pagesLeft != 0 && $currentPage <= $numPages) {

    if ($currentPage == $page) {
        echo "<div class='page-number-container'>
                    <img src='assets/img/page-selected.png'>
                    <span class='page-number'>$currentPage</span>
                  </div>";
    } else {

        echo "<div class='page-number-container'>
                    <a href='search.php?term=$term&type=$type&page=$currentPage'>
                      <img src='assets/img/page.png'>
                      <span class='page-number'>$currentPage</span>
                    </a>
                  </div>";

    }

    $currentPage++;
    $pagesLeft--;
}

?>

      <div class="page-number-container">
        <img src="assets/img/page-end.png">
      </div>

    </div>

   </div>

  </div>

  <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.js"></script>
  <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
  <script type="text/javascript" src="assets/js/script.js"></script>
</body>
</html>