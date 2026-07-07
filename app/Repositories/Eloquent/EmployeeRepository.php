<?php

namespace App\Repositories\Eloquent;

use App\DTOs\EmployeeDTO;
use App\Models\Employee;

class EmployeeRepository
{
    public function create(EmployeeDTO $DTO): Employee
    {
        return Employee::create($DTO->toArray());
    }
}
