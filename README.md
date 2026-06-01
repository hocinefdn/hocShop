├── src/
│ ├── Controller/ # Reçoit la Request, appelle le Service, retourne une JSONResponse unifiée
│ ├── Service/ # Logique métier (validation, orchestration)
│ ├── Repository/ # Requêtes SQL pures (via PDO) pour isoler la persistance
│ ├── Entity/ / Model/ # Représentation de l'objet Magasin
│ ├── DTO/ # Data Transfer Objects (ex: StoreCreateInput, StoreResponse)
│ ├── Exception/ # Exceptions métiers (ex: StoreNotFoundException)
│ └── Core/ # Routeur basique, Container DI ultra simple, Gestionnaire d'erreurs
