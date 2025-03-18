<?php

namespace App\Observers;

use App\Events\NewExpenseRecurringEvent;
use App\Models\ExpenseRecurring;
use App\Models\Notification;

class ExpenseRecurringObserver
{

    public function saving(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->last_updated_by = user()->id;
        }
    }

    public function creating(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->added_by = user()->id;
        }

        if (company()) {
            $expense->company_id = company()->id;
        }

        switch ($expense->rotation) {
        case 'daily':
            $days = $expense->issue_date->addDay();
            break;
        case 'weekly':
            $days = $expense->issue_date->addWeek();
            break;
        case 'bi-weekly':
            $days = $expense->issue_date->addWeeks(2);
            break;
        case 'monthly':
            $days = $expense->issue_date->addMonth();
            break;
        case 'quarterly':
            $days = $expense->issue_date->addQuarter();
            break;
        case 'half-yearly':
            $days = $expense->issue_date->addMonths(6);
            break;
        case 'annually':
            $days = $expense->issue_date->addYear();
            break;
        default:
            $days = $expense->issue_date->addDay();
        }

        $expense->next_expense_date = $days->format('Y-m-d');
    }

    public function created(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new NewExpenseRecurringEvent($expense, ''));
        }
    }

    public function updating(ExpenseRecurring $expense)
    {
        switch ($expense->rotation) {
            case 'daily':
                $days = $expense->issue_date->addDay();
                break;
            case 'weekly':
                $days = $expense->issue_date->addWeek();
                break;
            case 'bi-weekly':
                $days = $expense->issue_date->addWeeks(2);
                break;
            case 'monthly':
                $days = $expense->issue_date->addMonth();
                break;
            case 'quarterly':
                $days = $expense->issue_date->addQuarter();
                break;
            case 'half-yearly':
                $days = $expense->issue_date->addMonths(6);
                break;
            case 'annually':
                $days = $expense->issue_date->addYear();
                break;
            default:
                $days = $expense->issue_date->addDay();
            }

            $expense->next_expense_date = $days->format('Y-m-d');
    }

    public function updated(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($expense->isDirty('status')) {
                event(new NewExpenseRecurringEvent($expense, 'status'));
            }
        }
    }

    public function deleting(ExpenseRecurring $expense)
    {
        $notifyData = ['App\Notifications\NewExpenseRecurringMember', 'App\Notifications\ExpenseRecurringStatus'];
        Notification::deleteNotification($notifyData, $expense->id);

    }

}
