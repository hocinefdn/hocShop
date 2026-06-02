<?php

namespace App\Controller;

use App\Core\Container;
use App\Core\JsonResponse;
use App\Entity\Store;
use App\Repository\StoreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StoreController
{
    private StoreRepository $storeRepository;
    private Request $request;
    private ValidatorInterface $validator;

    public function __construct(Container $container)
    {
        $this->storeRepository = new StoreRepository($container->get(\PDO::class));
        $this->request = $container->get(Request::class);
        $this->validator = $container->get(ValidatorInterface::class);
    }

    /**
     * POST /api/stores
     */
    public function create(): void
    {
        // 1. Récupérer et décoder le body JSON de Symfony
        $content = json_decode($this->request->getContent(), true) ?? [];

        // 2. Hydrater l'entité Store avec les données reçues
        $store = new Store(
            null,
            $content['name'] ?? '',
            $content['address'] ?? '',
            $content['postal_code'] ?? '',
            $content['city'] ?? '',
            $content['is_active'] ?? true
        );

        // 3. Valider l'entité avec le Validator de Symfony
        $violations = $this->validator->validate($store);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                // Associe le champ à son message d'erreur
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            // On renvoie un code 400 standardisé avec la liste propre des erreurs
            JsonResponse::createStandard($errors, 400, 'error')->send();
        }

        // 4. Sauvegarde si tout est valide
        $savedStore = $this->storeRepository->save($store);

        // 5. Réponse 21 Created
        JsonResponse::createStandard($savedStore->toArray(), 201)->send();
    }

    // Conserve ton ancienne méthode index() ici...
}
