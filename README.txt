# 🌌 Bestiarium API

Une API REST en PHP natif pour créer et faire combattre des créatures mythologiques.

## 📋 Présentation

Bestiarium est une API permettant de :
- Se connecter / créer un compte
- Créer ou supprimer des monstres avec génération IA de leurs caractéristiques (stats, description et images)
- Fusionner des monstres pour créer des hybrides
- Organiser des combats épiques entre deux créatures

## 🛠 Technologies

- PHP 8+ (natif, sans framework)
- SQLite + PDO
- JWT (Firebase/php-jwt)
- Pollinations.ai (génération IA)
- Postman (tests API)

## ⚙️ Installation

1. Cloner le projet :
```bash
git clone https://github.com/votre-compte/bestiarium.git
cd bestiarium
```

2. Installer les dépendances :
```bash
composer install
```

3. Lancer le serveur :
```bash
php -S localhost:8000
```

## 🔑 Tests avec Postman

L'API utilise JWT pour l'authentification. Voici comment tester avec Postman :

1. Créer un compte :
```http
POST http://localhost:8000/register
Content-Type: application/json

{
    "username": "test",
    "email": "test@test.com",
    "password": "password123"
}
```

2. Se connecter pour obtenir un token :
```http
POST http://localhost:8000/login
Content-Type: application/json

{
    "email": "test@test.com",
    "password": "password123"
}
```

3. Utiliser le token reçu dans les requêtes suivantes :
```http
Headers:
Authorization: Bearer <votre_token_jwt>
Content-Type: application/json
```

## 📡 Points d'entrée API

### Authentification
- `POST /register` - Créer un compte
- `POST /login` - Se connecter
- `POST /logout` - Se déconnecter

### Monstres
- `POST /monster/create` - Créer un monstre
```json
{
    "name": "Dragon",
    "type": "Reptile",
    "heads": 3
}
```
- `GET /monster/all` - Liste des monstres
- `GET /monster/show/{id}` - Détails d'un monstre
- `DELETE /monster/delete/{id}` - Supprimer un monstre

### Hybrides
- `POST /hybrid/create` - Créer un hybride
```json
{
    "parent1_id": 1,
    "parent2_id": 2
}
```

### Combats
- `POST /battle/create` - Faire combattre deux monstres
```json
{
    "monster1_id": 1,
    "monster2_id": 2
}
```

## 📁 Structure du projet

```
bestiarium/
├── includes/
│   ├── controllers/     # Contrôleurs (auth, monsters, etc.)
│   ├── models/         # Classes modèles
│   ├── db/            # Connexion base de données
│   └── pollinations/  # Prompts pour l'IA
├── database/          # Base de données SQLite et Seeder pour tests rapides
├── vendor/           # Dépendances
└── index.php         # Point d'entrée
```

## 🔧 Tests rapides

Pour peupler la base de données avec des données de test :

```bash
php seed.php
```

Cela va créer :
- Un utilisateur de test (test@example.com / password123)
- 5 monstres de base (Dragon, Hydre, Griffon, Cerbère, Chimère)

La génération de ces données peut prendre un petit instant.

Ces données permettent de tester rapidement les fonctionnalités de l'API.