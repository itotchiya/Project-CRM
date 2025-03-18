<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\DashboardWidget
 *
 * @property int $id
 * @property string $widget_name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $dashboard_type
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget query()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereDashboardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereWidgetName($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereCompanyId($value)
 * @mixin \Eloquent
 */
class DashboardWidget extends BaseModel
{

    use HasCompany;

    protected $fillable = ['widget_name', 'status', 'dashboard_type', 'company_id'];

    const WIDGETS = [
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_clients',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_employees',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_projects',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_unpaid_invoices',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_hours_logged',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_pending_tasks',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_today_attendance',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_unresolved_tickets',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'recent_earnings',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'settings_leaves',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'new_tickets',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'overdue_tasks',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'pending_follow_up',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'project_activity_timeline',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'user_activity_timeline',  'status' => 1],
        ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'timelogs',  'status' => 1],

        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_clients',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_leads',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_lead_conversions',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_contracts_generated',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_contracts_signed',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'client_wise_earnings',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'client_wise_timelogs',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'lead_vs_status',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'lead_vs_source',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'latest_client',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'recent_login_activities',  'status' => 1],
        ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_deals',  'status' => 1],

        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_paid_invoices',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_expenses',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_earnings',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_pending_amount',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'invoice_overview',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'estimate_overview',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'proposal_overview',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'earnings_by_client',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'earnings_by_projects',  'status' => 1],
        ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_unpaid_invoices',  'status' => 1],

        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_leaves_approved',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_new_employee',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_employee_exits',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'average_attendance',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'department_wise_employee',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'designation_wise_employee',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'gender_wise_employee',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'role_wise_employee',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'leaves_taken',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'late_attendance_mark',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'headcount',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'joining_vs_attrition',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'birthday',  'status' => 1],
        ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_today_attendance',  'status' => 1],

        ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'total_project',  'status' => 1],
        ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'total_hours_logged',  'status' => 1],
        ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'total_overdue_project',  'status' => 1],
        ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'status_wise_project',  'status' => 1],
        ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'pending_milestone',  'status' => 1],

        ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'total_tickets',  'status' => 1],
        ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'total_unassigned_ticket',  'status' => 1],
        ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'type_wise_ticket',  'status' => 1],
        ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'status_wise_ticket',  'status' => 1],
        ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'channel_wise_ticket',  'status' => 1],
        ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'new_tickets',  'status' => 1],

        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'profile',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'shift_schedule',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'birthday',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'notices',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'tasks',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'projects',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'my_task',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'my_calender',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'week_timelog',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'leave',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'lead',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'work_from_home',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'appreciation',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'work_anniversary',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'ticket',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'notice_period_duration',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'probation_date',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'contract_date',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'internship_date',  'status' => 1],
        ['dashboard_type' => 'private-dashboard', 'widget_name' => 'follow_ups', 'status' => 1],
    ];

}
