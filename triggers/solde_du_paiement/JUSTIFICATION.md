# Pourquoi un TRIGGER pour l'agrégation du solde dû ?

## La règle métier

Quand un organisateur de match public n'a pas récupéré la totalité des 60€ (parce que le match s'est joué avec moins de 4 joueurs), il doit le solde restant à l'application. Ce solde bloque la création d'un nouveau match mais pas l'inscription comme joueur dans un match existant. Au moment où cet organisateur paie sa part dans un autre match, le solde dû est automatiquement ajouté à son paiement.

---

## Pourquoi un TRIGGER et pas un EVENT

La règle se déclenche **sur une action utilisateur** (un INSERT dans `Paiements`), pas sur le passage du temps. C'est exactement le cas d'usage d'un TRIGGER : réagir à un événement SQL précis.

Un EVENT n'aurait aucun sens ici — il n'y a pas de notion de "à telle heure, faire quelque chose". Le moment de déclenchement est imprévisible : c'est quand le membre clique sur "payer".

---

## Pourquoi BEFORE INSERT et pas AFTER INSERT

Le trigger intervient **avant** l'insertion du paiement pour pouvoir modifier le montant à insérer. Avec un AFTER INSERT, le paiement serait déjà enregistré avec le mauvais montant et il faudrait faire un UPDATE derrière, ce qui est moins propre et moins atomique.

---

## Ce que fait le TRIGGER

Au moment où un membre insère un paiement pour une inscription :

1. Il calcule le solde dû du membre : somme, sur toutes ses réservations `Etat = 'FORFAIT'`
   (matchs publics qu'il a organisés et qui se sont joués incomplets), du déficit
   `Prix_Total - paiements valides déjà reçus`.
2. Si ce solde est positif, il l'ajoute au montant du paiement en cours.
3. Il repasse ces réservations `FORFAIT` à `TERMINEE` : le solde est maintenant couvert.

Tout se passe en une seule transaction : le membre paie sa part + son solde en un seul INSERT.

Ce mécanisme ne peut fonctionner que grâce à `evt_cloture_matches`
(`triggers/cloture_matches/`), seul point du système à positionner `Etat = 'FORFAIT'` une
fois qu'un match public s'est joué sans ses 4 joueurs payés.

---

## Ce que ce trigger ne fait pas

Il ne bloque pas la réservation si un solde est dû — c'est le rôle de
`ReservationRepository::hasSoldeDu()`, appelé par `ReservationService::createReservation()`
avant toute création de match. Cette méthode recalcule exactement la même formule de
déficit (voir son commentaire dans le code, qui renvoie vers ce trigger) ; les deux doivent
rester synchronisés si la formule change un jour. Le trigger ne gère que le remboursement
automatique au moment du paiement — la validation métier reste dans la couche service, pas
dans une procédure stockée, pour respecter la séparation en couches du reste du projet.

---

## Résumé

| | Trigger `trg_agregation_solde_du` | `ReservationRepository::hasSoldeDu()` |
|---|---|---|
| Déclencheur | INSERT sur `Paiements` | Appel depuis `ReservationService::createReservation()` |
| Rôle | Agréger le solde dû au paiement | Bloquer la réservation si solde dû |
| Moment | Quand le membre paie | Quand le membre veut créer un match |
