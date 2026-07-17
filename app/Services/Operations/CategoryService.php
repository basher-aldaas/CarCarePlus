<?php

namespace App\Services\Operations;

use App\DTOs\CategoryDTO;
use App\Models\Category;
use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {
    }

    /**
     * List Categories
     */
    public function index(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    /**
     * Show Category
     */
    public function show(int $id): Category
    {
        return $this->categoryRepository->findById($id);
    }

    /**
     * Store Category
     */
    public function store(CategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($dto) {
            return $this->categoryRepository->create($dto);
        });
    }

    /**
     * Update Category
     */
    public function update(Category $category, CategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($category, $dto) {
            return $this->categoryRepository->update($category, $dto);
        });
    }

    /**
     * Delete Category
     */
    public function destroy(Category $category): bool
    {
        return $this->categoryRepository->delete($category);
    }
}
