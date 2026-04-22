# Pourquoi commencer par Sites et pas par Membres ou Administrateurs ?

## La règle : suivre les dépendances des clés étrangères

On commence toujours par les tables dont personne ne dépend, et on finit par les tables qui dépendent de tout le monde.

Une clé étrangère (FK) est une flèche qui dit "cette table a besoin que cette autre existe d'abord". Si tu essaies de créer `Terrains` avant `Sites`, MySQL te rejette parce que `Terrains.Site_ID` référence une table qui n'existe pas encore.

---

## Appliqué au schéma PadelManager

| Table | Dépend de (FK vers) |
|---|---|
| Sites | rien |
| Terrains | Sites |
| Horaires_Sites | Sites |
| Fermetures | Sites |
| Membres | Sites |
| Administrateurs | Sites |
| Reservations | Terrains, Membres |
| Inscriptions | Reservations, Membres |
| Paiements | Inscriptions |
| Penalites | Membres, Reservations, Administrateurs |

---

## Le graphe des dépendances

```
Sites ──┬─> Terrains ──────┐
        ├─> Horaires       │
        ├─> Fermetures     ├──> Reservations ──> Inscriptions ──> Paiements
        ├─> Membres ───────┤                          │
        └─> Administrateurs └──> Penalites <──────────┘
```

- **Sites** n'a aucune flèche entrante → c'est la racine → on commence par là.
- **Paiements** reçoit des flèches mais n'en envoie aucune → c'est une feuille → on la fait en dernier.

En informatique, ça s'appelle un **tri topologique** d'un graphe orienté. C'est un algorithme classique, et c'est exactement ce qu'on applique intuitivement ici.

---

## Pourquoi pas commencer par Membres ou Administrateurs ?

Parce que `Membres` dépend de `Sites` (un membre de catégorie `'S'` a un `Site_ID`). Même chose pour `Administrateurs` (un admin de type `'SITE'` a un `Site_ID`). Il faut donc que `Sites` existe avant.

L'intuition naturelle est de commencer par les "utilisateurs" parce qu'on pense que ce sont le point de départ conceptuel du système. Mais en base de données, le point de départ c'est ce qui ne dépend de rien — pas ce qui est conceptuellement le plus "important".

---

## Même logique pour coder le backend

L'ordre suivi dans le projet respecte exactement les mêmes règles :

1. **Sites** — pas de FK
2. **Terrains** — dépend de Sites
3. **Membres** — dépend de Sites
4. **Reservations** — dépend de Terrains + Membres
5. **Inscriptions** — dépend de Reservations + Membres
6. **Paiements** — dépend d'Inscriptions

Si `TerrainRepository` avait été codé avant `SiteRepository`, il aurait été impossible de valider que `Site_ID` existe. Si `ReservationService` avait été codé avant `MembreRepository`, il aurait été impossible de vérifier que l'organisateur existe.

---

## La méthode à retenir

Quand tu as une nouvelle base à modéliser :

1. Liste toutes les tables avec leurs FK
2. Trouve celles qui n'ont **aucune** FK sortante → ce sont tes **racines**, tu commences par elles
3. Ajoute ensuite les tables qui dépendent uniquement de celles déjà traitées
4. Continue jusqu'à ce que toutes les tables soient traitées

Pour la **suppression**, c'est l'inverse : les feuilles d'abord, les racines en dernier — sinon les contraintes de clé étrangère empêchent la suppression.
