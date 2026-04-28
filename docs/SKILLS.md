# Skills — Processus de développement

## Créer un modèle

### 1. Créer l'issue GitHub
- Titre : `Ajout du modèle NomModel`
- Corps : description, liste des propriétés (depuis le SQL), règles métier
- Commande : `gh issue create --title "..." --body "..."`

### 2. Créer le fichier sur `main`
- Créer `models/NomModel.php` sur la branche `main`
- Commit : `Ajout du modèle NomModel`
- Push sur `main` (le fichier doit être visible dans le projet)

### 3. Créer la branche orpheline
- Nom : `model-<nom>` (ex: `model-horairesite`, `model-site`)
- Branche orpheline : `git checkout --orphan model-<nom>`
- Supprimer tout : `git rm -rf .`
- Recréer uniquement `models/NomModel.php`
- Commit : `Ajout du modèle NomModel`
- Push : `git push -u origin model-<nom>`

### Règles
- La branche `model-<nom>` ne contient QUE `models/NomModel.php`
- Pas de PR, pas de branche `feat/`
- Le fichier doit aussi être sur `main` pour être visible dans le projet
- Sans co-auteur Claude dans les commits
