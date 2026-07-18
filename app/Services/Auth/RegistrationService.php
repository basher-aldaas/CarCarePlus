<?php

namespace App\Services\Auth;

use App\DTOs\AuhDTOs\RegisterCompanyCustomerDTO;
use App\DTOs\AuhDTOs\RegisterEmployeeDTO;
use App\DTOs\AuhDTOs\RegisterWorkshopDTO;
use App\DTOs\UserDTO;
use App\Enums\CompanyStatus;
use App\Enums\EmployeeType;
use App\Enums\WorkshopStatus;
use App\Models\Branch;
use App\Notifications\RegistrationPendingNotification;
use App\Notifications\StaffAccountCreatedNotification;
use App\Notifications\WelcomeNotification;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\EmployeeRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WorkshopRepository;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected CompanyRepository $companyRepository,
        protected WorkshopRepository $workshopRepository,
        protected EmployeeRepository $employeeRepository,
    ) {}

    /**
     * Type 1: Personal customer self-registration.
     * Account is active immediately and receives an auth token.
     *
     * @return array{user: \App\Models\User, token: string}
     */
    public function registerCustomer(UserDTO $dto): array
    {
        $result = DB::transaction(function () use ($dto) {
            $user = $this->userRepository->create($dto);
            $user->assignRole('customer_personal');

            $token = $user->createToken('auth_token')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        });

        $result['user']->notify(new WelcomeNotification());

        return $result;
    }

    /**
     * Type 2a: Company customer registration request.
     * Creates an inactive user + a pending company. No token until approved.
     *
     * @return array{user: \App\Models\User, company: \App\Models\Company}
     */
    public function registerCompany(RegisterCompanyCustomerDTO $dto): array
    {
        $result = DB::transaction(function () use ($dto) {
            $dto->userDto->is_active = false;
            $user = $this->userRepository->create($dto->userDto);
            $user->assignRole('customer_company');

            $dto->companyDto->customer_id = $user->id;
            $dto->companyDto->status = CompanyStatus::PENDING->value;
            $dto->companyDto->is_active = false;
            $company = $this->companyRepository->create($dto->companyDto);

            return ['user' => $user, 'company' => $company];
        });

        $result['user']->notify(new RegistrationPendingNotification(__('company')));

        return $result;
    }

    /**
     * Type 2b: Workshop registration request.
     * Creates an inactive user + a pending workshop. No token until approved.
     *
     * @return array{user: \App\Models\User, workshop: \App\Models\Workshop}
     */
    public function registerWorkshop(RegisterWorkshopDTO $dto): array
    {
        $result = DB::transaction(function () use ($dto) {
            $dto->user->is_active = false;
            $user = $this->userRepository->create($dto->user);
            $user->assignRole('workshop');

            $dto->workshop->user_id = $user->id;
            $dto->workshop->status = WorkshopStatus::PENDING->value;
            $workshop = $this->workshopRepository->create($dto->workshop);

            return ['user' => $user, 'workshop' => $workshop];
        });

        $result['user']->notify(new RegistrationPendingNotification(__('workshop')));

        return $result;
    }

    /**
     * Type 3: Super admin creates a staff account (washer / mechanic / admin).
     * The role is derived from the employee type. When the type is admin, the
     * user is also assigned as manager of their branch.
     *
     * @return array{user: \App\Models\User, employee: \App\Models\Employee}
     */
    public function createEmployee(RegisterEmployeeDTO $dto): array
    {
        $result = DB::transaction(function () use ($dto) {
            $user = $this->userRepository->create($dto->user);

            $user->assignRole($dto->employee->type->roleName());

            $dto->employee->user_id = $user->id;
            $employee = $this->employeeRepository->create($dto->employee);

            // A branch admin also manages the branch they belong to.
            if ($dto->employee->type === EmployeeType::ADMIN) {
                Branch::findOrFail($dto->branchId)->update(['admin_id' => $user->id]);
            }

            return ['user' => $user, 'employee' => $employee];
        });

        $accountType = $dto->employee->type === EmployeeType::ADMIN ? __('admin') : __('employee');
        $result['user']->notify(new StaffAccountCreatedNotification($accountType));

        return $result;
    }
}
