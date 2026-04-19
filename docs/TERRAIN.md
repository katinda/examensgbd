# Documentation - Terrain

## C'est quoi un Terrain ?

Un terrain c'est le court de padel physique sur lequel on joue.
Chaque terrain appartient à un site. Un site peut avoir plusieurs terrains.
La paire (Site_ID, Num_Terrain) est unique : deux sites peuvent avoir un "Terrain 1"
mais au sein d'un même site les numéros sont uniques.

---

## Fichiers créés

### `models/Terrain.php`
La fiche d'identité d'un terrain. Contient 5 propriétés :
- `Terrain_ID` : numéro unique du terrain
- `Site_ID` : clé étrangère vers le site auquel appartient ce terrain
- `Num_Terrain` : numéro du terrain dans le site (ex: 1, 2, 3...)
- `Libelle` : nom optionnel du terrain (ex: "Terrain Central")
- `Est_Actif` : true = terrain disponible, false = terrain fermé

Ne fait jamais de SQL. Stocke juste des données.

---

### `repositories/TerrainRepository.php`
Gère tout le SQL de la table Terrains. Contient 6 méthodes :
- `findAll()` → retourne tous les terrains
- `findById($id)` → retourne un terrain ou null
- `findBySiteId($siteId)` → retourne tous les terrains d'un site précis
- `insert($terrain)` → crée un nouveau terrain, retourne son ID
- `update($terrain)` → met à jour un terrain existant
- `delete($id)` → supprime un terrain

La connexion PDO est reçue en paramètre (injection de dépendance).

---

### `services/TerrainService.php`
Contient la logique métier des terrains. Utilise **deux repositories** :
`TerrainRepository` + `SiteRepository` (pour vérifier que le site existe).

Contient 5 méthodes :
- `getAllTerrains()` → retourne uniquement les terrains **actifs**
- `getTerrainById($id)` → retourne un terrain actif ou null
- `getTerrainsBySite($siteId)` → retourne les terrains actifs d'un site, null si site inexistant
- `createTerrain($data)` → vérifie que le site existe, gère le doublon (Site_ID, Num_Terrain)
- `updateTerrain($id, $data)` → met à jour, retourne false si inexistant
- `deleteTerrain($id)` → supprime, retourne false si inexistant

#### Gestion des erreurs spéciales dans createTerrain()
- Site inexistant → retourne `'site_introuvable'` → controller renvoie **404**
- Numéro déjà pris sur ce site → retourne `'doublon'` → controller renvoie **409 Conflict**

---

### `controllers/TerrainController.php`
Reçoit les requêtes HTTP et renvoie du JSON. Contient 6 méthodes :

| Méthode | Route | Description |
|---|---|---|
| `getAll()` | GET /terrains | Retourne tous les terrains actifs |
| `getById()` | GET /terrains/{id} | Retourne un terrain ou 404 |
| `getBySite()` | GET /sites/{id}/terrains | Retourne les terrains d'un site ou 404 |
| `create()` | POST /terrains | Crée un terrain (400, 404 ou 409 possible) |
| `update()` | PUT /terrains/{id} | Met à jour un terrain ou 404 |
| `delete()` | DELETE /terrains/{id} | Supprime un terrain ou 404 |

---

## Endpoints disponibles

```
GET    /terrains                  → liste tous les terrains actifs
GET    /terrains/{id}             → retourne un terrain précis
GET    /sites/{siteId}/terrains   → retourne les terrains d'un site
POST   /terrains                  → crée un nouveau terrain
PUT    /terrains/{id}             → met à jour un terrain
DELETE /terrains/{id}             → supprime un terrain
```

### Exemple POST /terrains
```json
{
    "site_id": 1,
    "num_terrain": 1,
    "libelle": "Terrain Central"
}
```

### Codes de réponse possibles
| Code | Signification |
|---|---|
| 201 | Terrain créé avec succès |
| 400 | Champs obligatoires manquants |
| 404 | Site ou terrain introuvable |
| 409 | Ce numéro de terrain existe déjà pour ce site |

---

## Tests unitaires

### `tests/TerrainRepositoryTest.php` — 7 tests
- `testFindAllRetourneTousLesTerrains`
- `testFindByIdRetourneLesBonTerrain`
- `testFindByIdRetourneNullSiInexistant`
- `testFindBySiteIdRetourneLesTerrainsduSite`
- `testFindBySiteIdRetourneVideSiAucunTerrain`
- `testInsertAjouteUnTerrain`
- `testUpdateModifieUnTerrain`
- `testDeleteSupprimeUnTerrain`

### `tests/TerrainServiceTest.php` — 11 tests
- `testGetAllTerrainsRetourneSeulementLesActifs`
- `testGetTerrainByIdRetourneNullSiInactif`
- `testGetTerrainByIdRetourneLeTerrainSiActif`
- `testGetTerrainsBySiteRetourneNullSiSiteInexistant`
- `testGetTerrainsBySiteRetourneLesTerrainsActifs`
- `testCreateTerrainRetourneSiteIntrouvable`
- `testCreateTerrainRetourneUnId`
- `testUpdateTerrainRetourneTrueSiExiste`
- `testUpdateTerrainRetourneFalseSiInexistant`
- `testDeleteTerrainRetourneTrueSiExiste`
- `testDeleteTerrainRetourneFalseSiInexistant`
