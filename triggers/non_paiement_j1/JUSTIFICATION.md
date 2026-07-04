# Pourquoi un EVENT MySQL pour le non-paiement à J-1 ?

## La règle métier

Si un joueur n'a pas payé sa part (15€) la veille du match, sa place est automatiquement libérée et réservable par quelqu'un d'autre. Une pénalité de 7 jours lui est appliquée.

---

## Pourquoi pas un TRIGGER classique

Même raison que pour le basculement privé→public : la règle se déclenche **sur le temps (J-1)**, pas sur une action d'un utilisateur. Un TRIGGER ne peut pas réagir au passage du temps.

---

## Pourquoi à 22h et pas 23h

Cet EVENT tourne à **22h**, une heure avant l'EVENT de basculement privé→public (23h).

L'ordre est important :
1. **22h** — on libère les places des joueurs non payés
2. **23h** — on vérifie si les matchs privés ont encore 4 joueurs

Si un match privé avait 4 joueurs mais que l'un n'a pas payé, sa place est d'abord libérée à 22h. Le match se retrouve avec 3 joueurs. À 23h, l'EVENT de basculement le détecte et le bascule en public avec pénalité sur l'organisateur.

---

## Deux objets distincts : EVENT + TRIGGER

Les deux responsabilités sont séparées pour respecter le rôle de chaque objet :

| Objet | Rôle | Déclencheur |
|---|---|---|
| **EVENT** | Détecte les non-paiements et supprime les inscriptions | Le temps (22h, J-1) |
| **TRIGGER AFTER DELETE** | Réagit à la suppression et crée la pénalité | Un DELETE sur `Inscriptions` |

---

## Ce que fait l'EVENT

Il cherche toutes les inscriptions qui répondent à ces conditions :
1. Le match a lieu demain (J-1)
2. Le match est en état `EN_COURS` ou `BASCULE_PUBLIC`
3. Aucun paiement valide n'existe pour cette inscription

Pour chaque inscription trouvée, il **supprime la ligne** → ce DELETE déclenche automatiquement le TRIGGER.

---

## Ce que fait le TRIGGER

Il réagit à chaque DELETE sur `Inscriptions`. Avant de créer une pénalité, il vérifie que la suppression concerne bien un cas de non-paiement à J-1 (et non une suppression manuelle par un admin). Si la condition est confirmée, il insère la pénalité `PAYMENT_MISSING`.

---

## Pourquoi supprimer l'inscription

La table `Inscriptions` n'a pas de colonne de statut. Supprimer la ligne est le seul moyen de libérer la place. L'historique du non-paiement est conservé dans la table `Penalites`.

---

## Résumé

| | EVENT non-paiement | EVENT basculement privé→public |
|---|---|---|
| Heure | 22h | 23h |
| Condition | Pas de paiement à J-1 | Moins de 4 joueurs à J-1 |
| Action 1 | Pénalité PAYMENT_MISSING | Basculement PUBLIC / BASCULE_PUBLIC |
| Action 2 | Suppression de l'inscription | Pénalité PRIVATE_INCOMPLETE |
