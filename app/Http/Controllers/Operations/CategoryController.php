<?php


namespace App\Http\Controllers\Operations;

use App\DTOs\CategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\Response;
use App\Models\Category;
use App\Services\Operations\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(
        CategoryService $categoryService
    )
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        return Response::Success(
            CategoryResource::collection(
                $this->categoryService->index()
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

    public function store(CategoryRequest $request)
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
        CategoryRequest $request,
        Category        $category
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
