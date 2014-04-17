<?php require ROOT_PATH . '/features/common/common-header.php'; ?>

  <link rel="stylesheet" href="/features/passwords/assets/passwords.css" type="text/css" media="screen" charset="utf-8" />
  <style type="text/css">
    .passwords-form { background-image: url('<?php echo photos_banner(); ?>'); }
  </style>
  <div class="passwords-form">
    <div class="passwords-mask"></div>

    <h3>Cryptage de mot de passe</h3>

    <div class="passwords-error"></div>

    <form name="passwordsform" id="passwordsform" action="/api/passwords" method="post">
      <p class="explanations">
        Cette page vous permet de crypter un mot de passe à ajouter dans le fichiers de la liste des utilisateurs. Indiquez simplement un mot de passe puis cliquez sur <strong>Crypter</strong>.
      </p>
      <p>
        <label for="password">
          Mot de passe<br />
          <input type="text" name="password" id="password" class="input" value="" size="20">
        </label>
      </p>

      <div class="passwords-result">
        <label>
          Mot de passe crypté<br />
          <div class="passwords-result-data"></div>
        </label>
      </div>

      <p class="submit">
        <input type="submit" class="button button-primary button-large" value="Crypter" />
      </p>
    </form>
  </div>

  <?php add_javascript('/features/passwords/assets/passwords.js') ?>

<?php require ROOT_PATH . '/features/common/common-footer.php'; ?>