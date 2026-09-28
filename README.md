# Web2Risk Agency

Site one page en HTML, CSS, JavaScript et PHP pour l’évaluation.

## Fichiers

- `index.html` : présentation, services, équipe et contact.
- `styles.css` : palette fournie, mise en page et adaptation mobile.
- `script.js` : menu mobile et envoi du formulaire sans recharger la page.
- `contact.php` : validation côté serveur et envoi avec `mail()`.
- `assets` : images et logos originaux du starter pack.
- `starter/textes` : documents sources en anglais.


## Aperçu local

Ouvrir `index.html` pour voir le site. Les polices Google Fonts nécessitent une connexion internet.

Pour exécuter aussi le formulaire, installer PHP 7.4 ou supérieur, puis lancer depuis ce dossier :

```sh
php -S localhost:8000
```

Ouvrir ensuite `http://localhost:8000`. Live Server ne peut pas exécuter PHP.

## Configurer les e-mails

Définir sur l’hébergement PHP les variables d’environnement `CONTACT_TO` (adresse de réception) et `CONTACT_FROM` (adresse d’envoi autorisée sur le domaine). Aucune adresse réelle n’a été inventée dans le code.

Le serveur doit disposer d’un service d’envoi configuré pour la fonction PHP `mail()`. Un serveur PHP local seul ne suffit pas. Sans configuration, le formulaire affiche une erreur et conserve les données saisies. Un retour positif de `mail()` signifie que le serveur a accepté le message, pas que sa livraison est garantie.

Vérifier avec un vrai envoi sur l’hébergement, puis contrôler la réception et le dossier spam. La protection comprend un champ piège et un délai de 60 secondes par session ; elle ne remplace pas une protection antispam de production.

## Vérifications manuelles

1. Vérifier les quatre liens de navigation et le retour en haut.
2. À une largeur mobile, ouvrir le menu, suivre un lien et le fermer avec Échap.
3. Vérifier le site au clavier et les champs obligatoires.
4. Sur un hébergement PHP configuré, envoyer un message et vérifier sa réception.
5. Sans configuration e-mail, vérifier que le formulaire ne prétend pas avoir envoyé le message.

