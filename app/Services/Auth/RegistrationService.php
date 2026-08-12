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
use App\Models\User;
use App\Models\UserPoint;
use App\Models\Wallet;
use App\Notifications\RegistrationPendingNotification;
use App\Notifications\StaffAccountCreatedNotification;
use App\Notifications\WelcomeNotification;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\EmployeeRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WorkshopRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /** Welcome points every newly created user starts with. */
    private const STARTING_POINTS = 10;

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
            $this->initBalances($user);

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
            $this->initBalances($user);

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
            $this->initBalances($user);

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
        $branch = null;

        if ($dto->employee->type === EmployeeType::ADMIN) {
            $branch = Branch::with('manager')->findOrFail($dto->branchId);

            // Placeholder admins (e.g. the super admin, seeded on branch creation)
            // may be replaced, but a branch already run by a real admin may not.
            if ($branch->manager && $branch->manager->hasRole('admin')) {
                throw ValidationException::withMessages([
                    'branch_id' => __('This branch already has an admin assigned.'),
                ]);
            }
        }

        $result = DB::transaction(function () use ($dto, $branch) {
            $user = $this->userRepository->create($dto->user);

            $user->assignRole($dto->employee->type->roleName());
            $this->initBalances($user);

            $dto->employee->user_id = $user->id;
            $employee = $this->employeeRepository->create($dto->employee);

            // A branch admin also manages the branch they belong to.
            if ($branch) {
                $branch->update(['admin_id' => $user->id]);
            }

            return ['user' => $user, 'employee' => $employee];
        });

        $accountType = $dto->employee->type === EmployeeType::ADMIN ? __('admin') : __('employee');
        $result['user']->notify(new StaffAccountCreatedNotification($accountType));

        return $result;
    }

    /**
     * Give a newly created user their starting balances: an empty wallet and
     * a small welcome points balance. Runs inside the caller's registration
     * transaction, so it rolls back with the user if anything fails.
     */
    private function initBalances(User $user): void
    {
        Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0],
        );

        UserPoint::firstOrCreate(
            ['customer_id' => $user->id],
            ['balance' => self::STARTING_POINTS],
        );
    }
}
