<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\EmployeeReportDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\EmployeeReportRequest\CreateEmployeeReportRequest;
use App\Http\Resources\EmployeeReportResource;
use App\Http\Responses\Response;
use App\Models\EmployeeReport;
use App\Services\Operations\EmployeeReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeReportController extends Controller
{
    public function __construct(
        protected EmployeeReportService $employeeReportService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['order_id', 'employee_id', 'status']);

        return Response::Success(
            data: EmployeeReportResource::collection($this->employeeReportService->index($request->integer('per_page', 15), $filters)),
            message: 'employee reports fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEmployeeReportRequest $request): JsonResponse
    {
        $employeeId = $request->user()->employee?->id;

        abort_unless($employeeId !== null, 403, __('Only employees can submit reports'));

        $dto = EmployeeReportDTO::fromArray($request->validated());

        $result = $this->employeeReportService->store($dto, $employeeId);

        return Response::Success(
            data: new EmployeeReportResource($result),
            message: 'employee report created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeReport $employeeReport): JsonResponse
    {
        return Response::Success(
            new EmployeeReportResource($this->employeeReportService->show($employeeReport)),
            'employee report fetched successfully'
        );
    }
}