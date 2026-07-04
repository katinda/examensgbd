# Pourquoi un EVENT pour clôturer les matchs ?

## Le trou que ça comble

`Reservations.Etat` prévoit deux états terminaux — `TERMINEE` (match joué normalement) et
`FORFAIT` (match joué incomplet, l'organisateur doit le solde) — mais rien ne les positionne
jamais. Résultat concret : le trigger `trg_agregation_solde_du`
(`triggers/solde_du_paiement/`) et `ReservationRepository::hasSoldeDu()` cherchent tous les
deux des réservations `Etat = 'FORFAIT'`, mais aucune ligne ne passe jamais à cet état. Le
recouvrement du solde et le blocage de nouvelles réservations restent morts tant que ce
mécanisme n'existe pas.

---

## Pourquoi un EVENT et pas un TRIGGER

La règle se déclenche **sur le passage du temps** ("le match a eu lieu"), pas sur une action
utilisateur précise (INSERT/UPDATE/DELETE). C'est le même raisonnement que pour
`evt_bascule_match_prive_public` : un TRIGGER ne peut pas réagir à "il est telle heure",
seul un EVENT le peut.

---

## Pourquoi 23h45, après les deux autres events

Séquence de la nuit :
1. **22h00** — `evt_liberer_places_non_paiement` libère les places non payées à J-1.
2. **23h00** — `evt_bascule_match_prive_public` bascule en public les matchs privés encore
   incomplets à J-1.
3. **23h45** — `evt_cloture_matches` (celui-ci) clôture les matchs dont l'heure de début est
   déjà passée.

Ces deux premiers events agissent la veille du match (J-1), pour donner encore une chance
de compléter les places. La clôture, elle, ne peut avoir lieu **qu'après le début effectif du
match** : un match public reste rejoignable ("premier payé, premier servi") jusqu'à son
coup d'envoi. Clôturer à J-1 couperait des inscriptions encore légitimes.

---

## Pourquoi comparer `TIMESTAMP(Date_Match, Heure_Debut) <= NOW()` et pas juste `Date_Match = CURDATE()`

Une simple égalité de date suppose que l'event tourne bien chaque nuit. En comparant
l'horodatage exact du match à l'instant présent, l'event reste correct même s'il a été
manqué une nuit (panne, maintenance) : au run suivant, il rattrape tous les matchs dont
l'heure de début est passée, pas seulement ceux d'hier.

---

## Ce que fait l'EVENT

Pour chaque réservation encore `EN_COURS` ou `BASCULE_PUBLIC` dont le match a déjà
commencé :
1. Compte les paiements valides (non annulés) parmi ses inscriptions.
2. 4 payés → `Etat = 'TERMINEE'` (match joué normalement, privé ou public).
3. Moins de 4 payés → `Etat = 'FORFAIT'` (ne peut arriver qu'à un match `PUBLIC`, puisque
   tout `PRIVE` incomplet a déjà basculé en public la veille).

---

## Ce que cet EVENT ne fait pas

Il ne calcule pas le montant du solde ni ne bloque de réservation — il se contente de
positionner l'état. Le calcul et l'usage du solde restent dans deux endroits qui doivent
rester cohérents entre eux :

| | Trigger `trg_agregation_solde_du` | `ReservationRepository::hasSoldeDu()` |
|---|---|---|
| Déclencheur | INSERT sur `Paiements` | Appel depuis `ReservationService::createReservation` |
| Rôle | Ajouter le solde dû au paiement suivant de l'organisateur | Bloquer la création d'une réservation tant que le solde n'est pas réglé |
| Formule | `Prix_Total - paiements valides`, sur les réservations `FORFAIT` de l'organisateur | Exactement la même formule |
