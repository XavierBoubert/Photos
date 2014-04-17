<?php global $siteConfig; ?>

  <footer><?php echo isset($siteConfig['footer']) ? $siteConfig['footer'] : ''; ?></footer>

  <script type="text/javascript" src="/vendor/jquery/jquery-2.1.0.min.js"></script>
  <script type="text/javascript" src="/vendor/magicsuggest/magicsuggest-1.3.1.js"></script>
  <script type="text/javascript" src="/features/common/assets/common-events.js"></script>
  <script type="text/javascript" src="/features/common/assets/common-templates.js"></script>
  <script type="text/javascript" src="/features/common/assets/common-page.js"></script>
  <?php javascripts(); ?>

</body>
</html>