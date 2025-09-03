<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domain\Company\Services\CompanyService;
use App\Domain\Company\Services\BranchService;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;
use App\Domain\Company\ValueObjects\BranchCode;
use App\Domain\Company\ValueObjects\WorkingSchedule;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\BranchId;

class TestCompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
        private readonly BranchService $branchService
    ) {}

    public function testCreateCompany(): JsonResponse
    {
        try {
            $company = $this->companyService->createCompany(
                CompanyName::fromString('Тестовая компания'),
                LegalName::fromString('ООО "Тестовая компания"'),
                INN::fromString('7707083893'), // Валидный ИНН ООО
                'г. Москва, ул. Тестовая, д. 1',
                'Описание тестовой компании',
                'https://test-company.ru',
                '+7 (495) 123-45-67',
                'info@test-company.ru'
            );

            return response()->json([
                'success' => true,
                'message' => 'Компания создана успешно',
                'company' => [
                    'id' => (string) $company->id(),
                    'name' => (string) $company->name(),
                    'legal_name' => (string) $company->legalName(),
                    'inn' => (string) $company->inn(),
                    'is_active' => $company->isActive(),
                    'has_events' => $company->hasEvents()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка создания компании: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testCreateBranch(): JsonResponse
    {
        try {
            // Получаем существующую компанию из базы данных
            $companies = $this->companyService->getAllCompanies();
            if (empty($companies)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет компаний в базе данных. Сначала создайте компанию.'
                ], 400);
            }

            $company = $companies[0]; // Берем первую компанию

            // Создаем расписание работы
            $workingSchedule = WorkingSchedule::fromArray([
                'monday' => ['is_working' => true, 'start' => '09:00', 'end' => '18:00', 'note' => 'Рабочий день'],
                'tuesday' => ['is_working' => true, 'start' => '09:00', 'end' => '18:00', 'note' => 'Рабочий день'],
                'wednesday' => ['is_working' => true, 'start' => '09:00', 'end' => '18:00', 'note' => 'Рабочий день'],
                'thursday' => ['is_working' => true, 'start' => '09:00', 'end' => '18:00', 'note' => 'Рабочий день'],
                'friday' => ['is_working' => true, 'start' => '09:00', 'end' => '18:00', 'note' => 'Рабочий день'],
                'saturday' => ['is_working' => false, 'start' => null, 'end' => null, 'note' => 'Выходной'],
                'sunday' => ['is_working' => false, 'start' => null, 'end' => null, 'note' => 'Выходной']
            ]);

            // Создаем филиал
            $branch = $this->branchService->createBranch(
                $company->id(),
                'Главный филиал',
                BranchCode::fromString('main'),
                'г. Санкт-Петербург, ул. Филиальная, д. 2',
                '+7 (812) 123-45-67',
                'spb@test-company.ru',
                $workingSchedule,
                '09:00',
                '18:00',
                59.9311,
                30.3609,
                'Главный филиал компании в Санкт-Петербурге'
            );

            return response()->json([
                'success' => true,
                'message' => 'Филиал создан успешно',
                'company' => [
                    'id' => (string) $company->id(),
                    'name' => (string) $company->name()
                ],
                'branch' => [
                    'id' => (string) $branch->id(),
                    'name' => $branch->name(),
                    'code' => (string) $branch->code(),
                    'is_main' => $branch->isMain(),
                    'is_working_today' => $branch->isWorkingToday(),
                    'is_working_now' => $branch->isWorkingNow(),
                    'has_events' => $branch->hasEvents()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка создания филиала: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testGetCompany(): JsonResponse
    {
        try {
            $companies = $this->companyService->getAllCompanies();

            $result = [];
            foreach ($companies as $company) {
                $result[] = [
                    'id' => (string) $company->id(),
                    'name' => (string) $company->name(),
                    'legal_name' => (string) $company->legalName(),
                    'inn' => (string) $company->inn(),
                    'is_active' => $company->isActive(),
                    'description' => $company->description(),
                    'phone' => $company->phone(),
                    'email' => $company->email()
                ];
            }

            return response()->json([
                'success' => true,
                'companies' => $result,
                'count' => count($result)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения компаний: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testGetBranches(): JsonResponse
    {
        try {
            $branches = $this->branchService->getAllBranches();

            $result = [];
            foreach ($branches as $branch) {
                $result[] = [
                    'id' => (string) $branch->id(),
                    'name' => $branch->name(),
                    'code' => (string) $branch->code(),
                    'address' => $branch->address(),
                    'is_active' => $branch->isActive(),
                    'is_main' => $branch->isMain(),
                    'is_working_today' => $branch->isWorkingToday(),
                    'is_working_now' => $branch->isWorkingNow(),
                    'working_days' => $branch->getWorkingDays()
                ];
            }

            return response()->json([
                'success' => true,
                'branches' => $result,
                'count' => count($result)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения филиалов: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testWorkingSchedule(): JsonResponse
    {
        try {
            $branches = $this->branchService->getAllBranches();

            if (empty($branches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет филиалов для тестирования'
                ]);
            }

            $branch = $branches[0];
            $schedule = $branch->workingSchedule();

            return response()->json([
                'success' => true,
                'branch' => [
                    'name' => $branch->name(),
                    'code' => (string) $branch->code()
                ],
                'working_schedule' => $schedule->getSchedule(),
                'is_working_today' => $schedule->isWorkingToday(),
                'is_working_now' => $schedule->isWorkingNow(),
                'working_days' => $schedule->getWorkingDays(),
                'next_working_day' => $schedule->getNextWorkingDay()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка тестирования расписания: ' . $e->getMessage()
            ], 500);
        }
    }
}
