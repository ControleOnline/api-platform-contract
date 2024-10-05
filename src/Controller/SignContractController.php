<?php

namespace ControleOnline\Controller;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use ControleOnline\Service\HydratorService;
use ControleOnline\Service\SignatureService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use ControleOnline\Entity\Contract;

class SignContractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private HydratorService $hydratorService,
        private SignatureService $signature,
    ) {}

    public function __invoke(Contract $data): Response
    {

        try {
            $data = $this->signature->sign($data);
            return new JsonResponse($this->hydratorService->data($data, 'contract:read'), Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($this->hydratorService->error($e));
        }
    }
}
