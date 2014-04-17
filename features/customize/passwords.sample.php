<?php

global $passwords;
global $admins;

$passwords = array(

  // Ajoutez un identifiant d'utilisateur puis son mot de passe crypté
  // Pour crypter facilement un mot de passe, rendez-vous sur la page http://<votre-site-de-photo>/passwords
  'admin' => '$2y$10$llz4SSd8XPW24FQvsZsyCet7fg9t7E.oWEF.Mvxcdi8QTa5e.ZG/2' // admin:admin

);


$admins = array(

  // Indiquez chaque identifiant dans ce tableau pour qu'il dispose des droits d'administration

  'admin'

);