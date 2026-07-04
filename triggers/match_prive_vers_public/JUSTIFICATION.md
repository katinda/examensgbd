# Pourquoi un EVENT MySQL et pas un TRIGGER classique ?

## Le problème

La règle métier dit : *"si les 4 joueurs ne sont pas atteints la veille du match, le match privé devient automatiquement public"*.

Cette règle doit se déclencher **à une heure précise (J-1)**, pas en réaction à une action d'un utilisateur.

---

## Pourquoi pas un TRIGGER classique

Un TRIGGER en SQL se déclenche uniquement sur des actions de données :
- `INSERT` → une ligne est insérée
- `UPDATE` → une ligne est modifiée
- `DELETE` → une ligne est supprimée

Il est **impossible** de déclencher un TRIGGER sur le passage du temps. Il ne peut pas "savoir" qu'on est à J-1 d'un match.

---

## Pourquoi un EVENT MySQL

Un EVENT MySQL est un **job planifié** directement dans la base de données. Il tourne automatiquement à l'heure choisie, sans intervention humaine et sans dépendre du back-end.

Avantages :
- La règle est **garantie au niveau de la base**, même si le back-end est en panne
- Aucun utilisateur ne peut l'oublier ou la contourner
- Tourne chaque soir à 23h sans intervention

---

## Ce que fait l'EVENT concrètement

Chaque soir à 23h, il cherche tous les matchs qui répondent à ces 3 critères :
1. Type = `PRIVE`
2. État = `EN_COURS`
3. Date du match = demain
4. Nombre de joueurs inscrits < 4

Pour chacun de ces matchs, il effectue deux actions **simultanément** (même exécution, même instant) :
1. Bascule le match → `Type = PUBLIC` / `Etat = BASCULE_PUBLIC`
2. Crée une pénalité de 7 jours sur l'organisateur → `Cause = PRIVATE_INCOMPLETE`

> L'ordre d'écriture est purement technique. Du point de vue métier, les deux conséquences s'appliquent en même temps.

---

## Résumé

| | TRIGGER | EVENT |
|---|---|---|
| Se déclenche sur | Une action SQL | Le temps |
| Adapté pour J-1 | Non | Oui |
| Garanti en base | Oui | Oui |
| Indépendant du back-end | Oui | Oui |
