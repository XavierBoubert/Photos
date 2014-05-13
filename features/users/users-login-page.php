<?php

  add_css('/features/users/assets/users-login.css');

  require ROOT_PATH . '/features/common/common-header.php';

?>

  <style type="text/css">
    .login-form { background-image: url('<?php echo photos_banner(); ?>'); }

    @media screen and (max-width: 800px) {
      body { background-image: url('<?php echo photos_banner(); ?>'); }
    }
  </style>

  <div class="login-container">
    <div class="login-background"></div>

    <div class="login-form">
      <div class="login-mask"></div>

      <h3>Connexion</h3>

      <div class="login-error"></div>

      <form name="loginform" id="loginform" action="/api/login" method="post">
        <p>
          <label for="user_login">
            Identifiant<br />
            <input type="text" name="login" id="user_login" class="input" value="" size="20">
          </label>
        </p>
        <p>
          <label for="user_pass">
            Mot de passe<br />
            <input type="password" name="password" id="user_pass" class="input" value="" size="20">
          </label>
        </p>
        <p class="submit">
          <input type="submit" class="button-submit" value="Se connecter" />
        </p>
      </form>
    </div>

  </div>

  <?php add_javascript('/features/users/assets/users-login.js') ?>

<?php require ROOT_PATH . '/features/common/common-footer.php'; ?>