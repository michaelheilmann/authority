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

  /* modal overlay */
  .modal-overlay {
    position: fixed;
    inset: 0;
    background: var(--model-overlay-color);
    display: none;
    align-items: center;
    justify-content: center;
  }
  
  /* modal */
  .modal-content {
    background-color: #fefefe;
    margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
   
    border-width: 1px;
    border-style: solid;
    border-color: var(--light-gray);
    border-radius: 10px;
   
    padding: 10px;
  }
 
  .modal-content {
    background: white;
    width: 24rem;
    padding: 0.5rem;
    border-radius: 10px;
    position: relative;
  }
  
  /* Form */
  .modal-content h2 {
    margin-bottom: 20px;
    text-align: center;
  }
  
  .modal-overlay p {
    color: black;
  }
  
  .modal-overlay label,
  .modal-overlay input,
  .modal-overlay button {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
  }
  
  .modal-overlay > form > div {
    display: flex;
    flex-direction: column
  }
  
  .modal-overlay input {
    padding: 10px;
      
    border-radius: 6px;
    border-width: 1px;
    border-style: solid;
    border-color: #ccc;
  }
  
  .modal-overlay form div[data-id="global"] {
    display:none;
  }

  .modal-overlay form.invalid div[data-id="global"] {
    display: block;
    margin-bottom: 16px;
    color: red;    
  }

  .modal-overlay input.invalid {
    padding: 10px;
      
    border-radius: 6px;
    border-width: 1px;
    border-style: solid;
    border-color: var(--border-color-error);
  }

  .modal-overlay button {
    padding: 12px;
    margin: 8px 0;
    background: var(--button-background-color);
    border: none;
    color: white;
    
    border-radius: 6px;
    
    cursor: pointer;
  }

  .modal-overlay button:hover {
    background: var(--button-background-color-hover);
  }

  .modal-overlay input+[aria-live='polite'] {
    color: red;
    font-size: 12px;
    display: none;
    margin-bottom: 16px;
  }

  .modal-overlay input+[aria-live='polite'] {
    display: block;
  }

  
  </style>  
  </head>
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
  <body>
    <header>
      <div class="logo"><a href="<?php echo AUTHORITY_WS_URL; ?>"><?php echo AUTHORITY_WS_TITLE;?></a></div>
      <div class="dynamic-space"></div>
      <button id="register-button" onclick="document.getElementById('register-dialog').style.display='block'">Register</button>
      <button id="login-button" onclick="document.getElementById('login-dialog').style.display='block'">Login</button>
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
    
    <div id="registration-successful-dialog" class="modal-overlay">
      <form class="modal-content animate">
        <h2>Registration Successful</h2>
        <div>
            <p>Your registration was succesful. Please login to your account <a style="color: green" onclick="dialogs.showDialog('login-dialog')" href="#">here</a>.</p>
        </div>
        <section>
            <button onclick="document.getElementById('registration-successful-dialog').style.display='none'">OK</button>
        </section>
      </form>
    </div>
   
    <div id="register-dialog" class="modal-overlay">
      <form class="modal-content animate">
        <h2>Register</h2>
        <div aria-live="polite" data-id="global"></div>
        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name" data-id="name">
            <span aria-live="polite"></span>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" data-id="password">
            <span aria-live="polite"></span>
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" data-id="email">
            <span aria-live="polite"></span>
        </div>
        <section>
            <button type="submit">Register</button>
        </section>
        <section>
        Already registered? <a style="color: green" onclick="dialogs.showDialog('login-dialog')" href="#">Login here</a>
        </section>
      </form>
    </div>

    <script>
    
      var dialogs = {
        
        elementIDs : [
          'registration-successful-dialog',
          'register-dialog',
          'login-successful-dialog',
          'login-dialog',
        ],
        
        onLoad : function() {
          window.addEventListener('click', function(e) {
            var modal = document.getElementById('login-dialog');
            if (event.target == modal) {
              modal.style.display = "none";
            }
          });

          window.addEventListener('click', function(e) {
            var modal = document.getElementById('register-dialog');
            if (event.target == modal) {
              modal.style.display = "none";
            }
          });

          window.addEventListener('click', function(e) {
            var modal = document.getElementById('registration-successful-dialog');
            if (event.target == modal) {
              modal.style.display = "none";
            }
          });

          window.addEventListener('click', function(e) {
            var modal = document.getElementById('login-successful-dialog');
            if (event.target == modal) {
              modal.style.display = "none";
            }
          });
        },
        
        hideDialog : function(name) {
            this.elementIDs.forEach((e) => {
            if (e == name) {
              document.getElementById(e).style.display = 'none';
            }
          });      
        },
        
        showDialog : function(name) {
          this.elementIDs.forEach((e) => {
            if (e == name) {
              document.getElementById(e).style.display = 'block';
            } else {
              document.getElementById(e).style.display = 'none';
            }
          });
        },
      };
    
      // Handle form submission.
      document.getElementById('register-dialog').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from refreshing the page
        
        let apiUrl = "<?php echo AUTHORITY_API_URL; ?>" + 'register';

        // Capture the fields.
        const dialogElement = document.getElementById('register-dialog');
        const formElement = dialogElement.getElementsByTagName('form')[0];
        const nameElement = dialogElement.querySelector('[data-id="name"]');
        const passwordElement = dialogElement.querySelector('[data-id="password"]');
        const emailElement = dialogElement.querySelector('[data-id="email"]');
        const globalElement = dialogElement.querySelector('[data-id="global"]');
        
        fetch(apiUrl, {
          method : 'POST',
          body : JSON.stringify({ 'name' : nameElement.value,
                                  'password' : passwordElement.value,
                                  'email' : emailElement.value }) 
        }).then((response) => {
            if (!response.ok) {
              const contentType = response.headers.get('Content-Type')
              if (contentType && contentType.includes('application/json')) {
                // return a rejected Promise that includes the JSON
                return response.json().then((json) => Promise.reject(json))
              } else {
                throw new Error('invalid response')
              }
            }
            const contentType = response.headers.get('Content-Type')
            if (contentType && contentType.includes('application/json')) {
              return response.json();
            } else {
              throw new Error('invalid response')
            }
          })
          .then(data => {
            formElement.classList.remove('invalid');
            nameElement.classList.remove('invalid');
            passwordElement.classList.remove('invalid');
            emailElement.classList.remove('invalid');
            
            dialogs.showDialog('registration-successful-dialog');
          })
          .catch(error => {
            var x;
            
            // (1) per-field error messages
            x = error['field-errors'];
            if (x.hasOwnProperty('name')) {
              nameElement.classList.add('invalid');
              nameElement.nextElementSibling.innerText = x['name'][0];
            } else {
              nameElement.classList.remove('invalid');
            }
            if (x.hasOwnProperty('password')) {
              passwordElement.classList.add('invalid');
              passwordElement.nextElementSibling.innerText = x['password'][0];
            } else {
              passwordElement.classList.remove('invalid');
            }
            if (x.hasOwnProperty('email')) {
              emailElement.classList.add('invalid');
              emailElement.nextElementSibling.innerText = x['email'][0];
            } else {
              emailElement.classList.remove('invalid');
            }
            
            // (2) global error messsages
            x = error['global-errors'];
            if (x.length) {
              formElement.classList.add('invalid');
              globalElement.innerText = x[0];
            } else {
              formElement.classList.remove('invalid');
            }
          });       
      });
    </script>

    <div id="login-successful-dialog" class="modal-overlay">
      <form class="modal-content animate">
        <h2>Registration Successful</h2>
        <div>
            <p>Your login was succesful.</p>
        </div>
        <section>
            <button onclick="document.getElementById('login-successful-dialog').style.display='none'">OK</button>
        </section>
      </form>
    </div>
  
    <div id="login-dialog" class="modal-overlay">
      <form class="modal-content animate" action="/login.php" method="post">
        <h2>Login</h2>
        <div aria-live="polite" data-id="global"></div>
        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name" data-id="name">
            <span aria-live="polite"></span>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" data-id="password">
            <span aria-live="polite"></span>
        </div>
        <section>
            <button type="submit">Login</button>
        </section>
        <section>
        Not registered? <a style="color: green" onclick="dialogs.showDialog('register-dialog')" href="#">Register</a>
        </section>
      </form>
    </div>
  
    <script>
      // Handle form submission.
      document.getElementById('login-dialog').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from refreshing the page

        let apiUrl = "<?php echo AUTHORITY_API_URL; ?>" + 'login';

        // Capture the fields.
        const dialogElement = document.getElementById('login-dialog');
        const formElement = dialogElement.getElementsByTagName('form')[0];
        const nameElement = dialogElement.querySelector('[data-id="name"]');
        const passwordElement = dialogElement.querySelector('[data-id="password"]');
        const globalElement = dialogElement.querySelector('[data-id="global"]');
        
        fetch(apiUrl, {
          method : 'POST',
          body : JSON.stringify({ 'name' : nameElement.value,
                                  'password' : passwordElement.value }) 
        }).then((response) => {
            if (!response.ok) {
              const contentType = response.headers.get('Content-Type')
              if (contentType && contentType.includes('application/json')) {
                // return a rejected Promise that includes the JSON
                return response.json().then((json) => Promise.reject(json))
              } else {
                throw new Error('invalid response')
              }
            }
            const contentType = response.headers.get('Content-Type')
            if (contentType && contentType.includes('application/json')) {
              return response.json();
            } else {
              throw new Error('invalid response')
            }
          })
          .then(data => {
            formElement.classList.remove('invalid');
            nameElement.classList.remove('invalid');
            passwordElement.classList.remove('invalid');
            
            dialogs.showDialog('login-successful-dialog');
          })
          .catch(error => {
            var x;
            
            // (1) per-field error messages
            x = error['field-errors'];
            if (x.hasOwnProperty('name')) {
              nameElement.classList.add('invalid');
              nameElement.nextElementSibling.innerText = x['name'][0];
            } else {
              nameElement.classList.remove('invalid');
            }
            if (x.hasOwnProperty('password')) {
              passwordElement.classList.add('invalid');
              passwordElement.nextElementSibling.innerText = x['password'][0];
            } else {
              passwordElement.classList.remove('invalid');
            }
            
            // (2) global error messages
            x = error['global-errors'];
            if (x.length) {
              formElement.classList.add('invalid');
              globalElement.innerText = x[0];
            } else {
              formElement.classList.remove('invalid');
            }
          });
      });
    </script>
  
  </body>
</html>

<script>
dialogs.onLoad();
</script>
