<?php

namespace ControleOnline\Controller;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use ControleOnline\Service\HydratorService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use ControleOnline\Entity\Contract;
use ControleOnline\Service\ContractService;

class GenerateContractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private HydratorService $hydratorService,
        private ContractService $contract,
    ) {}

    public function __invoke(Contract $data): Response
    {

        try {

            $this->contract->genetateFromModel($data);

            return new JsonResponse($this->hydratorService->data($data, 'contract_read'), Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($this->hydratorService->error($e));
        }
    }
}
