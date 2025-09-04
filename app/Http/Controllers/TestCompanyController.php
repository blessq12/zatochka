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

    public function testSetBranchAsMain(): JsonResponse
    {
        try {
            $branches = $this->branchService->getAllBranches();

            if (empty($branches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет филиалов для тестирования'
                ], 400);
            }

            $branch = $branches[0];
            $companyId = $branch->companyId();

            // Устанавливаем филиал как главный
            $updatedBranch = $this->branchService->setBranchAsMain($branch->id());

            // Получаем все филиалы компании для проверки
            $companyBranches = $this->branchService->getBranchesByCompanyId($companyId);

            $mainBranches = array_filter($companyBranches, fn($b) => $b->isMain());
            $mainBranch = reset($mainBranches);

            return response()->json([
                'success' => true,
                'message' => 'Филиал установлен как главный',
                'branch' => [
                    'id' => (string) $updatedBranch->id(),
                    'name' => $updatedBranch->name(),
                    'code' => (string) $updatedBranch->code(),
                    'is_main' => $updatedBranch->isMain(),
                    'has_events' => $updatedBranch->hasEvents()
                ],
                'company_branches' => array_map(function ($b) {
                    return [
                        'id' => (string) $b->id(),
                        'name' => $b->name(),
                        'is_main' => $b->isMain()
                    ];
                }, $companyBranches),
                'main_branch_id' => $mainBranch ? (string) $mainBranch->id() : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка установки главного филиала: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testDeleteBranch(): JsonResponse
    {
        try {
            $branches = $this->branchService->getAllBranches();

            if (empty($branches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет филиалов для тестирования'
                ], 400);
            }

            $branch = $branches[0];
            $branchId = $branch->id();
            $branchName = $branch->name();

            // Проверяем, что филиал существует
            if (!$this->branchService->branchExists($branchId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Филиал не найден'
                ], 404);
            }

            // Удаляем филиал
            $this->branchService->deleteBranch($branchId);

            // Проверяем, что филиал больше не существует
            $deletedBranch = $this->branchService->getBranchById($branchId);
            $stillExists = $this->branchService->branchExists($branchId);

            return response()->json([
                'success' => true,
                'message' => 'Филиал удален успешно',
                'deleted_branch' => [
                    'id' => (string) $branchId,
                    'name' => $branchName
                ],
                'verification' => [
                    'branch_still_exists' => $stillExists,
                    'deleted_branch_object' => $deletedBranch ? 'exists' : 'null'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка удаления филиала: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testBranchCompanyRelationship(): JsonResponse
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            if (empty($companies)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет компаний для тестирования'
                ], 400);
            }

            $company = $companies[0];
            $companyId = $company->id();

            // Получаем все филиалы компании
            $companyBranches = $this->branchService->getBranchesByCompanyId($companyId);
            $activeBranches = $this->branchService->getActiveBranchesByCompanyId($companyId);
            $mainBranch = $this->branchService->getMainBranchByCompanyId($companyId);
            $branchCount = $this->branchService->countBranchesByCompanyId($companyId);

            return response()->json([
                'success' => true,
                'company' => [
                    'id' => (string) $company->id(),
                    'name' => (string) $company->name(),
                    'legal_name' => (string) $company->legalName()
                ],
                'branches' => [
                    'total_count' => $branchCount,
                    'all_branches' => array_map(function ($b) {
                        return [
                            'id' => (string) $b->id(),
                            'name' => $b->name(),
                            'code' => (string) $b->code(),
                            'is_active' => $b->isActive(),
                            'is_main' => $b->isMain(),
                            'company_id' => (string) $b->companyId()
                        ];
                    }, $companyBranches),
                    'active_branches' => array_map(function ($b) {
                        return [
                            'id' => (string) $b->id(),
                            'name' => $b->name(),
                            'is_active' => $b->isActive()
                        ];
                    }, $activeBranches),
                    'main_branch' => $mainBranch ? [
                        'id' => (string) $mainBranch->id(),
                        'name' => $mainBranch->name(),
                        'code' => (string) $mainBranch->code()
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка тестирования связи филиал-компания: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testActivateDeactivateCompany(): JsonResponse
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            if (empty($companies)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет компаний для тестирования'
                ], 400);
            }

            // Берем первую компанию для тестирования
            $company = $companies[0];
            $companyId = $company->id();

            // Тестируем деактивацию
            $deactivatedCompany = $this->companyService->deactivateCompany($companyId);

            // Тестируем активацию
            $activatedCompany = $this->companyService->activateCompany($companyId);

            return response()->json([
                'success' => true,
                'message' => 'Тест активации/деактивации компании прошел успешно',
                'company' => [
                    'id' => (string) $company->id(),
                    'name' => (string) $company->name(),
                    'initial_status' => $company->isActive(),
                    'after_deactivation' => $deactivatedCompany->isActive(),
                    'after_activation' => $activatedCompany->isActive(),
                    'has_events' => $activatedCompany->hasEvents()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка тестирования активации/деактивации: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testUpdateCompany(): JsonResponse
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            if (empty($companies)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет компаний для тестирования'
                ], 400);
            }

            // Берем первую компанию для тестирования
            $company = $companies[0];
            $companyId = $company->id();

            // Обновляем компанию
            $updatedCompany = $this->companyService->updateCompany(
                $companyId,
                CompanyName::fromString('Обновленная компания'),
                LegalName::fromString('ООО "Обновленная компания"'),
                'г. Москва, ул. Обновленная, д. 100',
                'Описание обновленной компании',
                'https://updated-company.ru',
                '+7 (495) 999-99-99',
                'updated@company.ru'
            );

            return response()->json([
                'success' => true,
                'message' => 'Компания обновлена успешно',
                'company' => [
                    'id' => (string) $updatedCompany->id(),
                    'name' => (string) $updatedCompany->name(),
                    'legal_name' => (string) $updatedCompany->legalName(),
                    'address' => $updatedCompany->legalAddress(),
                    'description' => $updatedCompany->description(),
                    'website' => $updatedCompany->website(),
                    'phone' => $updatedCompany->phone(),
                    'email' => $updatedCompany->email(),
                    'has_events' => $updatedCompany->hasEvents()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка обновления компании: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testFindCompanyByInn(): JsonResponse
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            if (empty($companies)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет компаний для тестирования'
                ], 400);
            }

            // Берем первую компанию для поиска по ИНН
            $company = $companies[0];
            $inn = $company->inn();

            // Ищем компанию по ИНН
            $foundCompany = $this->companyService->getCompanyByInn($inn);

            if (!$foundCompany) {
                return response()->json([
                    'success' => false,
                    'message' => 'Компания не найдена по ИНН'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Компания найдена по ИНН',
                'search_inn' => (string) $inn,
                'found_company' => [
                    'id' => (string) $foundCompany->id(),
                    'name' => (string) $foundCompany->name(),
                    'legal_name' => (string) $foundCompany->legalName(),
                    'inn' => (string) $foundCompany->inn(),
                    'is_active' => $foundCompany->isActive()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка поиска компании по ИНН: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testCompanyExists(): JsonResponse
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            if (empty($companies)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет компаний для тестирования'
                ], 400);
            }

            // Берем первую компанию для проверки существования
            $company = $companies[0];
            $companyId = $company->id();
            $inn = $company->inn();

            // Проверяем существование по ID
            $existsById = $this->companyService->companyExists($companyId);

            // Проверяем существование по ИНН
            $existsByInn = $this->companyService->companyExistsByInn($inn);

            // Проверяем несуществующую компанию
            $notExistsById = $this->companyService->companyExists(99999);

            return response()->json([
                'success' => true,
                'message' => 'Тест проверки существования компании прошел успешно',
                'company' => [
                    'id' => (string) $companyId,
                    'name' => (string) $company->name(),
                    'inn' => (string) $inn
                ],
                'existence_checks' => [
                    'exists_by_id' => $existsById,
                    'exists_by_inn' => $existsByInn,
                    'not_exists_by_id_99999' => $notExistsById
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка проверки существования компании: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testGetActiveCompanies(): JsonResponse
    {
        try {
            // Получаем все активные компании
            $activeCompanies = $this->companyService->getAllActiveCompanies();

            $result = [];
            foreach ($activeCompanies as $company) {
                $result[] = [
                    'id' => (string) $company->id(),
                    'name' => (string) $company->name(),
                    'legal_name' => (string) $company->legalName(),
                    'inn' => (string) $company->inn(),
                    'is_active' => $company->isActive(),
                    'description' => $company->description()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Получен список активных компаний',
                'active_companies' => $result,
                'count' => count($result),
                'total_companies' => count($this->companyService->getAllCompanies())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения активных компаний: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testDeleteCompany(): JsonResponse
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            if (empty($companies)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет компаний для тестирования'
                ], 400);
            }

            // Берем последнюю компанию для тестирования удаления
            $company = end($companies);
            $companyId = $company->id();
            $companyName = $company->name();

            // Проверяем, что компания существует
            if (!$this->companyService->companyExists($companyId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Компания не найдена'
                ], 404);
            }

            // Удаляем компанию
            $this->companyService->deleteCompany($companyId);

            // Проверяем, что компания помечена как удаленная
            $deletedCompany = $this->companyService->getCompanyById($companyId);
            $stillExists = $this->companyService->companyExists($companyId);

            return response()->json([
                'success' => true,
                'message' => 'Компания удалена успешно',
                'deleted_company' => [
                    'id' => (string) $companyId,
                    'name' => (string) $companyName
                ],
                'verification' => [
                    'company_still_exists' => $stillExists,
                    'deleted_company_object' => $deletedCompany ? 'exists' : 'null',
                    'is_deleted' => $deletedCompany ? $deletedCompany->isDeleted() : 'unknown'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка удаления компании: ' . $e->getMessage()
            ], 500);
        }
    }
}
