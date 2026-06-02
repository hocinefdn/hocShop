# WSHOP — Test Technique Back-end PHP

> Candidature : Développeur Back-end PHP  
> Livrable : Archive ZIP — 3 tests + ce README  
> Contact : hocinefedani@gmail.com

---

## 📋 Sommaire

1. [Prérequis](#prérequis)
2. [Installation & Démarrage](#installation--démarrage)
3. [Test 3 — API REST & Endpoints](#test-3-—-api-rest)
4. [Qualité du code](#qualité-du-code)

---

## Prérequis

| Outil          | Version minimale          |
| -------------- | ------------------------- |
| Docker         | 24+                       |
| Docker Compose | v2+                       |
| Client HTTP    | Postman / Insomnia / cURL |

> Aucune installation locale de PHP ou Composer n'est nécessaire — tout tourne dans Docker.

---

## Installation & Démarrage

### 1. Cloner / décompresser l'archive

```bash
unzip wshop-test.zip && cd wshop-test
```

### 2. Démarrer l'environnement

```bash
docker-compose up -d
```

> L'application est accessible sur **http://localhost:8080**

### 3. Installer les dépendances

```bash
docker-compose exec app composer install
```

### 4. Initialiser la base de données

```bash
docker-compose exec db mysql -u root -proot wshop_db < database/schema.sql
```

> Le script crée les tables `users` et `stores`, et insère des données de test
> (7 magasins sur plusieurs villes + 1 utilisateur admin).

| Champ    | Valeur         |
| -------- | -------------- |
| Email    | admin@wshop.fr |
| Password | password123    |

## Test 3 — API REST

### Base URL

```
http://localhost:8080/api
```

### Architecture

| Couche          | Rôle                                               |
| --------------- | -------------------------------------------------- |
| `Controllers/`  | Réception HTTP, dispatch, réponse JSON             |
| `Services/`     | Logique métier                                     |
| `Repositories/` | Accès base de données (PDO)                        |
| `DTO/`          | Objets de transfert de données typés               |
| `Validators/`   | Validation stricte des entrées (Symfony Validator) |
| `Middleware/`   | Authentification JWT                               |

---

### 🔒 Authentification

Tous les endpoints `/api/stores` sont protégés. Générez un token puis ajoutez-le à chaque requête.

#### Obtenir un token

```http
POST /api/login
Content-Type: application/json

{
  "username": "admin@wshop.fr",
  "password": "password123"
}
```

**Réponse 200 :**

```json
{
  "status": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

#### Utiliser le token

```http
Authorization: Bearer <votre_token>
```

---

### Endpoints `/api/stores`

#### GET /api/stores — Liste (filtre & tri)

```bash
curl -X GET "http://localhost:8080/api/stores?city=Paris&sort=name&direction=ASC" \
  -H "Authorization: Bearer <token>"
```

Paramètres optionnels :

| Paramètre     | Type   | Exemple                      |
| ------------- | ------ | ---------------------------- |
| `city`        | string | `Paris`                      |
| `postal_code` | string | `75001`                      |
| `is_active`   | bool   | `true`                       |
| `sort`        | string | `name`, `city`, `created_at` |
| `direction`   | string | `ASC`, `DESC`                |

**Réponse 200 :**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Boutique Paris Centre",
      "address": "12 Rue de Rivoli",
      "postal_code": "75001",
      "city": "Paris",
      "is_active": true,
      "created_at": "2026-06-02 14:30:00"
    }
  ]
}
```

---

#### GET /api/stores/{id} — Détail

```bash
curl -X GET "http://localhost:8080/api/stores/1" \
  -H "Authorization: Bearer <token>"
```

**Réponse 404 :**

```json
{
  "status": "error",
  "message": "Magasin introuvable."
}
```

---

#### POST /api/stores — Créer

```bash
curl -X POST "http://localhost:8080/api/stores" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Wshop Lyon",
    "address": "45 Rue de la République",
    "postal_code": "69002",
    "city": "Lyon",
    "is_active": true
  }'
```

**Réponse 201 :**

```json
{
  "status": "success",
  "message": "Magasin créé avec succès.",
  "data": { "id": 2, "name": "Wshop Lyon", "..." }
}
```

**Réponse 400 (validation) :**

```json
{
  "status": "error",
  "message": "Erreur de validation des données.",
  "errors": {
    "name": "Le nom du magasin ne peut pas être vide.",
    "postalCode": "Le code postal doit contenir exactement 5 chiffres."
  }
}
```

---

#### PUT /api/stores/{id} — Mettre à jour

```bash
curl -X PUT "http://localhost:8080/api/stores/1" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Wshop Lyon Refait",
    "address": "45 Rue de la République",
    "postal_code": "69002",
    "city": "Lyon",
    "is_active": false
  }'
```

**Réponse 200 :**

```json
{
  "status": "success",
  "message": "Magasin mis à jour avec succès."
}
```

---

#### DELETE /api/stores/{id} — Supprimer

```bash
curl -X DELETE "http://localhost:8080/api/stores/1" \
  -H "Authorization: Bearer <token>"
```

**Réponse 200 :**

```json
{
  "status": "success",
  "message": "Magasin supprimé avec succès."
}
```

---

### Codes d'erreur globaux

| Code  | Cause                            |
| ----- | -------------------------------- |
| `400` | Erreur de validation des données |
| `401` | Token manquant ou invalide       |
| `404` | Ressource introuvable            |
| `500` | Erreur serveur interne           |

---

## Qualité du code

### Tests unitaires (PHPUnit)

```bash
docker-compose exec app ./vendor/bin/phpunit

```

### Analyse statique (PHPStan — niveau 6)

```bash
docker-compose exec app php -d memory_limit=-1 ./vendor/bin/phpstan analyse

```

### Formateur (PHP-CS-Fixer)

```bash
docker-compose exec app ./vendor/bin/php-cs-fixer fix

```

---

_PHP 8.2+ · Pas de framework applicatif · Docker · JWT · PHPStan · PHPUnit · PHP-CS-Fixer_
