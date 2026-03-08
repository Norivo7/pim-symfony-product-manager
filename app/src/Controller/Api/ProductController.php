<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use App\Request\Product\CreateProductRequest;
use App\Request\Product\ListProductsRequest;
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
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/products')]
final class ProductController extends AbstractController
{
    #[OA\Post(
        path: '/api/products',
        summary: 'Create product',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateProductRequest::class))
        ),
        tags: ['Product']
    )]
    #[OA\Response(
        response: 201,
        description: 'Product created successfully'
    )]
    #[OA\Response(
        response: 409,
        description: 'SKU conflict'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation failed'
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid request payload'
    )]
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

    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Get product details with price history',
        tags: ['Product']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Product ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Product details returned successfully'
    )]
    #[OA\Response(
        response: 404,
        description: 'Product not found'
    )]
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

    #[OA\Put(
        path: '/api/products/{id}',
        summary: 'Update product',
        tags: ['Product']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Product ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: UpdateProductRequest::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Product updated successfully'
    )]
    #[OA\Response(
        response: 404,
        description: 'Product not found'
    )]
    #[OA\Response(
        response: 409,
        description: 'SKU conflict or stale product version'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation failed'
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid request payload'
    )]
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

    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Soft delete product',
        tags: ['Product']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Product ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Product deleted successfully'
    )]
    #[OA\Response(
        response: 404,
        description: 'Product not found'
    )]
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

    #[OA\Get(
        path: '/api/products',
        summary: 'List products with pagination and optional status filter',
        tags: ['Product']
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Filter by product status',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'draft'])
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Response(
        response: 200,
        description: 'Product list returned successfully'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation failed'
    )]
    #[Route('', name: 'api_products_list', methods: ['GET'])]
    public function list(
        Request $request,
        ValidatorInterface $validator,
        ProductRepository $productRepository,
    ): JsonResponse {
        $listProductsRequest = new ListProductsRequest();

        $listProductsRequest->status = $request->query->get('status');
        $listProductsRequest->page = max(1, $request->query->getInt('page', 1));
        $listProductsRequest->limit = max(1, $request->query->getInt('limit', 10));

        $violations = $validator->validate($listProductsRequest);

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

        $products = $productRepository->findPaginated(
            $listProductsRequest->status,
            $listProductsRequest->page,
            $listProductsRequest->limit,
        );

        $total = $productRepository->countActive($listProductsRequest->status);

        $items = [];

        foreach ($products as $product) {
            $items[] = [
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
            ];
        }

        return $this->json([
            'items' => $items,
            'pagination' => [
                'page' => $listProductsRequest->page,
                'limit' => $listProductsRequest->limit,
                'total' => $total,
                'pages' => ceil($total / $listProductsRequest->limit),
            ],
            'filters' => [
                'status' => $listProductsRequest->status,
            ],
        ]);
    }
}
