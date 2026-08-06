<?php


namespace App\Http\Controllers\Operations;

use App\DTOs\CategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CategoryRequest\CreateCategoryRequest;
use App\Http\Requests\OperationsRequest\CategoryRequest\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\Response;
use App\Models\Category;
use App\Services\Operations\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    )
    {
    }

    public function index(Request $request)
    {
        return Response::Success(
            CategoryResource::collection(
                $this->categoryService->index($request->integer('per_page', 15))
            ),
            'Categories fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new CategoryResource(
                $this->categoryService->show($id)
            ),
            'Category fetched successfully'
        );
    }

    public function store(CreateCategoryRequest $request)
    {
        $dto = CategoryDTO::fromArray(
            $request->validated()
        );

        $category = $this->categoryService
            ->store($dto);

        return Response::Success(
            new CategoryResource($category),
            'Category created successfully'
        );
    }

    public function update(
        UpdateCategoryRequest $request,
        Category              $category
    ): JsonResponse
    {
        $dto = CategoryDTO::fromArray(
            $request->validated()
        );

        $category = $this->categoryService
            ->update($category, $dto);

        return Response::Success(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService
            ->destroy($category);

        return Response::Success(
            [],
            'Category deleted successfully'
        );
    }
}
