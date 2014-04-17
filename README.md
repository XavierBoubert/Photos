Photos
=========
_Entire project in French (for now)_

* Testez directement le site démo : http://demo-photos.xavierboubert.fr (identifiant : _demo_, mot de passe : _demo_)

_Photos_ vous permet de créer une magnifique gallerie pour toutes vos photos et vidéos. Son principe est de scanner un répertoire contenant des sous répertoires de photos et vidéos et de créer une interface pour les voir et les administrer facilement.

Attention. Cette interface n'influe jamais sur vos photos et vidéos directement. Toutes ses fonctionnalités n'influe que sur le cache des miniatures qu'il génère. Il vous ai impossible par exemple de supprimer une photo via l'interface, seulement de la cacher.

* [Licence](https://github.com/XavierBoubert/Photos/blob/master/LICENSE)
* [Changelog](https://github.com/XavierBoubert/Photos/blob/master/CHANGELOG.md)
* [Plan des prochaines fonctionnalités](https://github.com/XavierBoubert/Photos/blob/master/MILESTONE.md)
* Site web de l'auteur : [Xavier Boubert](http://xavierboubert.fr)


Installation
---------

_Photos_ ne demande qu'un simple server Apache avec PHP pour fonctionner.

1. Récupérer le repo sur votre machine avec `git clone` ou en récupérant l'archive de la dernière version
2. Ajouter vos répertoires de photos et vidéos dans le répertoire `photos` à la racine du projet ou tout simplement en faisant un `symlink` vers un répertoire contenant vos photos et vidéos

<dl>
  <dt>Attention</dt>
  <dd>Les répertoires contenant vos photos doivent avoir le format de nom suivant :
    "Photos 2012.12.31 Titre"
    Vous n'êtes pas obligé de donner un titre mais la date doit être impérativement dans le format "Année.Mois.Jour"</dd>
</dl>

3. Lancez le site du projet


Création de l'album
---------

La création de l'album se fait automatiquement lorsque vous naviguer sur le site. Une barre de progression affiche le nombre d'élements restant à traiter pour en générer le cache. Les photos, vidéos et albums générés apparaissent directement sur l'interface et peuvent être utilisé sans devoir rafraichir la page.
Une petite animation de chargement présente sur un album indique que son contenu est en train d'être généré. Vous pouvez donc voir son nombre de photos et vidéos évolué au fil de la progression.


Personnalisations
---------

Pour personnaliser votre album, le répertoire `features/customize` contient des fichiers dont les noms se terminent par _sample_. Copiez ces fichiers dans le même emplacement puis supprimez _.sample_ pour activer leurs personnalisations.

Par exemple `customize.sample.php` doit être copié en `customize.php` pour qu'il soit executé par le site.

Ces fichiers contiennent suffisament d'aide pour que vous les utilisiez facilement.


Sécurité et comptes utilisateurs
---------

Le site généré par le projet _Photos_ est entièrement sécurisé. Il est impossible à un visiteur de voir une photo sans être connecté au site. Même avec un lien direct vers la photos, elle sera remplacé par une image par défaut.

Pour ajouter des comptes utilisateurs avec des mots de passes cryptés, il vous faut utiliser le fichier `features/customize/passwords.sample.php` (en le copiant en `passwords.sample.php`).
Vous pouvez crypter des mots de passe en vous rendant sur une page spéciale du site : `http://<votre-site-de-photo>/passwords`