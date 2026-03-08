<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use App\Request\Product\CreateProductRequest;
use App\Request\Product\UpdateProductRequest;
use App\Service\Product\CreateProductService;
use App\Service\Product\DeleteProductService;
use App\Service\Product\UpdateProductService;
use Doctrine\ORM\OptimisticLockException;
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

    #[Route('/{id}', name: 'api_products_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UpdateProductService $updateProductService,
    ): JsonResponse {
        try {
            /** @var UpdateProductRequest $updateProductRequest */
            $updateProductRequest = $serializer->deserialize(
                $request->getContent(),
                UpdateProductRequest::class,
                'json'
            );
        } catch (ExceptionInterface) {
            return $this->json([
                'message' => 'Invalid request payload.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $violations = $validator->validate($updateProductRequest);

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
            $product = $updateProductService->handle(
                $id,
                $updateProductRequest->name,
                $updateProductRequest->sku,
                $updateProductRequest->price,
                $updateProductRequest->currency,
                $updateProductRequest->status,
                $updateProductRequest->version,
            );
        } catch (\DomainException $exception) {
            $statusCode = Response::HTTP_CONFLICT;

            if ($exception->getMessage() === 'Product not found.') {
                $statusCode = Response::HTTP_NOT_FOUND;
            }

            return $this->json([
                'message' => $exception->getMessage(),
            ], $statusCode);
        } catch (OptimisticLockException) {
            return $this->json([
                'message' => 'Product has been modified. Please refresh and try again.',
            ], Response::HTTP_CONFLICT);
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
            'priceHistory' => $priceHistory,
        ]);
    }

    #[Route('/{id}', name: 'api_products_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        DeleteProductService $deleteProduct,
    ): JsonResponse {
        try {
            $deleteProduct->handle($id);
        } catch (\DomainException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}
