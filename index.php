<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once(__DIR__ . '/frontend/' . "include.php");

?>
<!DOCTYPE html>
<html>
<head lang="en">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta charset="utf-8">
  <meta name="description" content="<?php echo AUTHORITY_WS_DESCRIPTION; ?>">
  
  <?php
    if (count(AUTHORITY_WS_KEYWORDS) > 0) {
      echo '<meta name="keywords" content="';
      echo implode(', ', AUTHORITY_WS_KEYWORDS);
      echo '">';   
    }   
  ?>
  <meta name="author" content="<?php echo AUTHORITY_WS_AUTHOR; ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  <title><?php echo AUTHORITY_WS_TITLE; ?></title>
  <link rel="stylesheet" href="index.css?v=1" />
  <style>



  </style>
  </head>
  <body>
    <header>
<?php
  $viewModel = new ViewModel();

  $activePage = $viewModel->getActivePage();
  $activeCategory = $viewModel->getActiveCategory();

  if ($activeCategory == 'organizations') {
    $itemCount = $viewModel->getNumberOfOrganizations();
  } else {
    $itemCount = $viewModel->getNumberOfPersons();
  }
  $itemsPerPage = 10;
  $numberOfPages = ceil($itemCount / $itemsPerPage);
?>

      <div class="logo">
        <a href="<?php echo AUTHORITY_WS_URL; ?>"><?php echo AUTHORITY_WS_TITLE;?></a>
      </div>
      <div></div>
      <button id="login-button" onclick="document.getElementById('login-dialog').style.display='block'"">Login</button>
    </header>
    <main id="middle-row">
      <div class="column-2">
      </div>
      <div class="column-6">
      <?php
      if ($activeCategory == 'organizations') {
        $result = $viewModel->getOrganizations($activePage, $itemsPerPage);
        $result = $result !== null ? $result['elements'] : $result;
      } else {
        $result = $viewModel->getPersons($activePage, $itemsPerPage);
        $result = $result !== null ? $result['elements'] : $result;
      }
      echo '<div class="row-9">';
        echo '<table class="data-table">';
        if ($activeCategory == 'organizations') {
          echo '<thead><tr><th>Name</th><th>Tags</th></tr></thead>';
          if ($result !== null) {
            foreach ($result as $organization) {
              echo '<tr>';
              echo '<td>' . $organization['name'] . '</td>';
              $tags = $viewModel->getOrganizationTags($organization['unique-id']);
              if (IsMyError($tags)) {
                echo '<td>failed to load tags' . $tags->asJson() . '</td>';
              } else {
                echo '<td>';
                echo implode(', ', array_map(function($tag) { return $tag['name']; }, $tags));
                echo '</td>';
              }
              echo '</tr>';
            }
          }          
        } else {
          echo '<thead><tr><th>Prename</th><th>Surname</th><th>Tags</th></tr></thead>';
          if ($result !== null) {
            foreach ($result as $person) {
              echo '<tr>';
              echo '<td>' . $person['prename'] . '</td>';
              echo '<td>' . $person['surname'] . '</td>';
              $tags = $viewModel->getPersonTags($person['unique-id']);
              if (IsMyError($tags)) {
                echo '<td>failed to load tags' . $tags->asJson() . '</td>';
              } else {
                echo '<td>';
                echo implode(', ', array_map(function($tag) { return $tag['name']; }, $tags));
                echo '</td>';
              }
              echo '</tr>';   
            }
          }
        }
        echo '</table>';
      echo '</div>';

      echo '<div class="row-1">';
        echo '<div class="column-1"></div>';
        echo '<nav class="column-8 pagination horizontal">';
         for ($i = 1; $i <= $numberOfPages; $i++) {
          echo '<a class="button" ' . ($i == $activePage ? 'class=\'active\'' : '') . ' href=\'' . AUTHORITY_WS_URL . '?category=' . $activeCategory . '&page=' . $i . '\'>' . $i . '</a>';      
        } 
        echo '</nav>';
        echo '<div class="column-1"></div>';
      echo '</div>';
    ?>
    </div>
    <div class="column-2">
      <nav id="right-column" class="row-1 category vertical">
        <a class="button" href="<?php echo AUTHORITY_WS_URL . "?category=persons"; ?>">Persons</a>
        <a class="button" href="<?php echo AUTHORITY_WS_URL . "?category=organizations"; ?>">Organizations</a>
      </nav>
    </div>
    </main>
    <footer class="row-1">
      <div class="column-1"></div>
      <div class="column-8">
      <?php include_once(__DIR__ . '/frontend/' . 'footer.php'); ?>
      </div>
      <div class="column-1"></div>
    </footer>
    <div id="login-dialog">
        <form class="modal-content animate" action="/login.php" method="post">
        </form>
    </div>
  </body>
</html>

<script>
<?php
// get the modal
?>
var modal = document.getElementById('login-dialog');
<?php
// when the user clicks anywhere outside of the modal, close it
?>
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
