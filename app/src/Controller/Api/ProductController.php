<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use App\Request\Product\CreateProductRequest;
use App\Service\Product\CreateProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products')]
final class ProductController extends AbstractController
{
    #[Route('', name: 'api_products_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CreateProductService $createProductService,
    ): JsonResponse
    {
        try {
            /** @var CreateProductRequest $createProductRequest */
            $createProductRequest = $serializer->deserialize(
                $request->getContent(),
                CreateProductRequest::class,
                'json'
            );
        } catch (ExceptionInterface) {
            return $this->json([
                'message' => 'Invalid request payload.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $violations = $validator->validate($createProductRequest);

        if (count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return $this->json([
                'message' => 'Validation failed.',
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $product = $createProductService->handle(
                $createProductRequest->name,
                $createProductRequest->sku,
                $createProductRequest->price,
                $createProductRequest->currency,
                $createProductRequest->status,
            );
        } catch (\DomainException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        }

        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'currency' => $product->getCurrency()->value,
            'status' => $product->getStatus()->value,
            'createdAt' => $product->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $product->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'deletedAt' => $product->getDeletedAt()?->format(\DateTimeInterface::ATOM),
            'version' => $product->getVersion(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_products_get', methods: ['GET'])]
    public function get(
        int               $id,
        ProductRepository $productRepository,
    ): JsonResponse
    {
        $product = $productRepository->findActiveById($id);

        if (!$product) {
            return $this->json([
                'message' => 'Product not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $priceHistory = [];

        foreach ($product->getPriceHistoryEntries() as $entry) {
            $priceHistory[] = [
                'oldPrice' => $entry->getOldPrice(),
                'newPrice' => $entry->getNewPrice(),
                'changedAt' => $entry->getChangedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'currency' => $product->getCurrency()->value,
            'status' => $product->getStatus()->value,
            'createdAt' => $product->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $product->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'deletedAt' => $product->getDeletedAt()?->format(\DateTimeInterface::ATOM),
            'version' => $product->getVersion(),
            'priceHistory' => $priceHistory
        ]);
    }

}
