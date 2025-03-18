<?php

namespace App\Exports;

use App\Models\LeaveType;
use App\Models\Leave;
use App\Models\User;
use App\Scopes\ActiveScope;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Carbon\Carbon;

class LeaveQuotaReportExport implements FromCollection, WithStyles, WithEvents
{
    /**
     * @return Collection
     */
    public $viewAttendancePermission;
    public $userId;
    public $forMontDate;
    public $thisMonthStartDate;

    public function __construct($id, $year, $month)
    {
        $this->viewAttendancePermission = user()->permission('view_attendance');
        $this->userId = $id;
        $this->forMontDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $this->thisMonthStartDate = now()->startOfMonth();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $employees = User::with([
            'employeeDetail',
             'employeeDetail.designation',
             'employeeDetail.department',
             'country',
             'employee',
             'roles'
             ])
            ->onlyEmployee()
            ->when(!$this->thisMonthStartDate->eq($this->forMontDate), function($query) {
                $query->with([
                    'leaveQuotaHistory' => function($query) {
                        $query->where('for_month', $this->forMontDate);
                    },
                    'leaveQuotaHistory.leaveType',
                ])->whereHas('leaveQuotaHistory', function ($query) {
                    $query->where('for_month', $this->forMontDate);
                });
            })
            ->when($this->thisMonthStartDate->eq($this->forMontDate), function($query) {
                $query->with([
                    'leaveTypes',
                    'leaveTypes.leaveType',
                ]);
            })
            ->withoutGlobalScope(ActiveScope::class)
            ->when($this->userId != 'all', function ($query) {
                $query->where('id', $this->userId);
            })
            ->get();

        $employeeData = collect();
        $months = collect();
        $sr = 1;
        for ($i = 1; $i <= request()->month; $i++) {
            $months->push(Carbon::create(null, $i, 1)->format('F'));
        }

        $leaveTypes = LeaveType::get();

        $leaveTypeHeaders = [];

        foreach ($leaveTypes as $leaveType) {
            $leaveTypeHeaders[] = $leaveType->type_name . ' (' . ($leaveType->paid == 1 ? __('app.paid') : __('app.unpaid')) . ') ' . __('modules.leaves.leavesTaken');
            $leaveTypeHeaders[] = $leaveType->type_name . ' (' . ($leaveType->paid == 1 ? __('app.paid') : __('app.unpaid')) . ') ' . __('modules.leaves.remainingLeaves');
        }

        $monthHeaders = $months->map(function ($month) {
            return $month;
        })->toArray();

        $employeeData->push([
            '#',
            __('app.name'),
            ...$monthHeaders,
            __('app.futureLeaves'),
            ...$leaveTypeHeaders,
            __('modules.leaves.leavesTaken'),
            __('modules.leaves.remainingLeaves'),
            __('app.totalLeave'),
        ]);

        foreach ($employees as $employee) {
            $leaveData = [];
            $leaveQuotaHistory = $this->getAllowedLeavesQuota($employee);

            foreach ($months as $month) {
                $leavesTakenInMonth = Leave::where('user_id', $employee->id)
                    ->whereMonth('leave_date', Carbon::parse($month)->month)
                    ->whereYear('leave_date', Carbon::now()->year)
                    ->where('status', 'approved')
                    ->get()
                    ->sum(function($leave) {
                        return $leave->half_day_type ? 0.5 : 1;
                    });
                $leaveData[] = (string)$leavesTakenInMonth;
            }

            $futureLeaves = Leave::where('user_id', $employee->id)
                ->where('leave_date', '>', Carbon::now()->endOfMonth())
                ->where('status', 'approved')
                ->get()
                ->sum(function($leave) {
                    return $leave->half_day_type ? 0.5 : 1;
                });

            $leaveData[] = (string)$futureLeaves;

            foreach ($leaveTypes as $leaveType) {
                $history = $leaveQuotaHistory->where('leave_type_id', $leaveType->id)->first();
                $leaveData[] = (string)($history ? $history->leaves_used : '0');
                $leaveData[] = (string)($history ? $history->leaves_remaining : '0');
            }

            $rowData = [
                $sr++,
                $employee->name,
                ...$leaveData,
                $leaveQuotaHistory->unique('leave_type_id')->sum('leaves_used') ?: '0',
                $leaveQuotaHistory->unique('leave_type_id')->sum('leaves_remaining') ?: '0',
                $leaveQuotaHistory->unique('leave_type_id')->sum('no_of_leaves') ?: '0',
            ];

            $employeeData->push($rowData);
        }

        return $employeeData;
    }

    protected function getAllowedLeavesQuota($employee)
    {
        if (!$this->thisMonthStartDate->eq($this->forMontDate)) {
            return $employee->leaveQuotaHistory;
        }

        $leaveQuotas = $employee->leaveTypes;
        $allowedLeavesQuota = collect([]);

        foreach ($leaveQuotas as $leaveQuota) {
            if ($leaveQuota->leaveType && ($leaveQuota->leaveType->leaveTypeCondition($leaveQuota->leaveType, $employee))) {
                $allowedLeavesQuota->push($leaveQuota);
            }
        }

        return $allowedLeavesQuota;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Apply styles to the entire sheet or specific cells
            'A1:Z1000' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $columns = range('A', $sheet->getHighestColumn());

                foreach ($columns as $column) {
                    $maxLength = 0;
                    foreach ($sheet->getRowIterator() as $row) {
                        $cell = $sheet->getCell($column . $row->getRowIndex());
                        $cellValue = $cell->getValue();
                        $cellLength = strlen($cellValue);
                        if ($cellLength > $maxLength) {
                            $maxLength = $cellLength;
                        }
                    }
                    // Add some padding to the width
                    $sheet->getColumnDimension($column)->setWidth($maxLength + 2);
                }

                $rowIterator = $sheet->getRowIterator();

                foreach ($rowIterator as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(20); // Set the desired row height
                }

                $styleArray = [
                    'font' => [
                        'bold' => true,
                    ],
                ];
                $sheet->getStyle('1:1')->applyFromArray($styleArray);

            }
        ];
    }

}
