<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CategoryDTO;
use App\Models\Category;

class CategoryRepository
{
    public function getAll()
    {
        return Category::latest()->get();
    }

    public function findById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function create(CategoryDTO $dto): Category
    {
        return Category::create($dto->toArray());
    }

    public function update(Category $category, CategoryDTO $dto): Category
    {
        $category->update($dto->toArray());

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
