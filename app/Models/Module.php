<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

/**
 * App\Models\Module
 *
 * @property int $id
 * @property string $module_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $customPermissions
 * @property-read int|null $custom_permissions_count
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Module newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Module newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Module query()
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissionsAll
 * @property-read int|null $permissions_all_count
 * @mixin \Eloquent
 */
class Module extends BaseModel
{

    protected $guarded = ['id'];

    const MODULE_LIST = [
        [
            'module_name' => 'clients',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_clients',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'view_clients',

                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'edit_clients',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'delete_clients',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_client_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_client_subcategory',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,

                    'is_custom' => 1,
                    'name' => 'add_client_contacts',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,

                    'is_custom' => 1,
                    'name' => 'view_client_contacts',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,

                    'is_custom' => 1,
                    'name' => 'edit_client_contacts',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_client_contacts',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_client_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_client_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_client_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_client_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_client_document',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_client_document',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_client_document',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_client_document',
                ],
            ]
        ],
        [
            'module_name' => 'employees',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_employees',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_employees',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_employees',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'delete_employees',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_designation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_designation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_designation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_designation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_department',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_department',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_department',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_department',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_documents',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_documents',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_documents',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_documents',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_leaves_taken',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'update_leaves_quota',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_employee_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_employee_projects',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_employee_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'change_employee_role',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_emergency_contact',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_award',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_appreciation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_appreciation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_appreciation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_appreciation',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5,
                    'is_custom' => 1,
                    'name' => 'add_immigration',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_immigration',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_immigration',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_immigration',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_increment_promotion',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_increment_promotion',
                ],
            ]
        ],
        [
            'module_name' => 'projects',
            'description' => 'User can view the basic details of projects assigned to him even without any permission.',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_projects',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_projects',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_projects',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_projects',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_project_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_project_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_project_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_discussions',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_project_discussions',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_project_discussions',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_project_discussions',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_discussion_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_project_milestones',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_project_milestones',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_project_milestones',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_project_milestones',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_members',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_project_members',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_project_members',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_project_members',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_project_rating',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_project_rating',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_project_rating',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_project_rating',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_budget',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_expenses',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_invoices',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_burndown_chart',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_payments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_gantt_chart',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_project_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_project_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_project_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_project_note',

                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'manage_project_template',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_template',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_hourly_rates',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'create_public_project',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_miroboard',
                ],
            ]

        ],
        [
            'module_name' => 'attendance',
            'description' => 'User can view his own attendance even without any permission.',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_employee_shifts',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_shift_roster',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_attendance',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_attendance',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_attendance',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_attendance',
                ],
            ]

        ],
        [
            'module_name' => 'tasks',
            'description' => 'User can view the tasks assigned to him even without any permission.',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'add_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_task_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_task_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_task_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_task_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_task_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'add_task_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_task_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_sub_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'add_sub_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_sub_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_sub_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_task_comments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'add_task_comments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_task_comments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_task_comments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_task_notes',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'add_task_notes',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_task_notes',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_task_notes',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'task_labels',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'change_status',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'send_reminder',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_status',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_unassigned_tasks',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'create_unassigned_tasks',
                ],
            ]
        ],
        [
            'module_name' => 'estimates',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_estimates',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_estimates',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_estimates',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_estimates',
                ],
            ]

        ],
        [
            'module_name' => 'invoices',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_invoices',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_invoices',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_invoices',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_invoices',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_tax',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'link_invoice_bank_account',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_recurring_invoice',
                ],
            ]
        ],
        [
            'module_name' => 'payments',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_payments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_payments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_payments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_payments',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'link_payment_bank_account',
                ]
            ]
        ],
        [
            'module_name' => 'timelogs',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0,
                    'name' => 'add_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'approve_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_active_timelogs',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_timelog_earnings',
                ],
            ]

        ],
        [
            'module_name' => 'tickets',
            'description' => 'User can view the tickets generated by him as default even without any permission.',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0,
                    'name' => 'add_tickets',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_tickets',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_tickets',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_tickets',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_ticket_type',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'manage_ticket_agent',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_ticket_channel',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_ticket_tags',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_ticket_groups',
                ],

            ]

        ],
        [
            'module_name' => 'events',
            'description' => 'User can view the events to be attended by him as default even without any permission.',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_events',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_events',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_events',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_events',
                ]
            ]

        ],
        [
            'module_name' => 'notices',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_notice',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_notice',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_notice',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_notice',
                ],
            ]

        ],
        [
            'module_name' => 'leaves',
            'description' => 'User can view the leaves applied by him as default even without any permission.',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'add_leave',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_leave',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_leave',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_leave',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'approve_or_reject_leaves',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_approve_leaves',
                ],
            ]
        ],
        [
            'module_name' => 'leads',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'add_lead',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_lead',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_lead',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_lead',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'display_name' => 'Manage Lead Custom Forms',
                    'is_custom' => 1,
                    'name' => 'manage_lead_custom_forms',
                ],

                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_lead_sources',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_lead_sources',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_lead_sources',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_lead_sources',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_lead_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_lead_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_lead_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_lead_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_lead_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_lead_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_lead_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_lead_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_deals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_deals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_deals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_deals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_deal_stages',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'change_deal_stages',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_lead_agents',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_lead_agent',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_lead_agent',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_lead_agent',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_lead_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_lead_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_lead_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_lead_follow_up',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_lead_follow_up',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_lead_follow_up',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_lead_follow_up',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_lead_proposals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_lead_proposals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_lead_proposals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_lead_proposals',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_proposal_template',
                ],

                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'add_deal_pipeline',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_deal_pipeline',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_deal_pipeline',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_deal_pipeline',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'add_deal_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_deal_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'edit_deal_note',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'delete_deal_note',
                ],
            ]
        ],
        [
            'module_name' => 'holidays',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_holiday',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_holiday',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_holiday',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_holiday',
                ],
            ]
        ],
        [
            'module_name' => 'products',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_product',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'view_product',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'edit_product',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'delete_product',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_product_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_product_sub_category',
                ],
            ]
        ],
        [
            'module_name' => 'expenses',
            'description' => 'User can view and add(self expenses) the expenses as default even without any permission.',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 0,
                    'name' => 'add_expenses',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_expenses',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_expenses',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_expenses',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_expense_category',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_recurring_expense',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'approve_expenses',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'link_expense_bank_account',
                ]
            ]
        ],
        [
            'module_name' => 'contracts',
            'description' => 'User can view all contracts',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_contract',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_contract',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_contract',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_contract',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_contract_type',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'renew_contract',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_contract_discussion',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'edit_contract_discussion',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_contract_discussion',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_contract_discussion',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_contract_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'view_contract_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'delete_contract_files',
                ],
                [
                    'allowed_permissions' => Permission::ALL_ADDED_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_contract_template',
                ],
            ]
        ],
        [
            'module_name' => 'reports',
            'description' => 'User can manage permission of particular report',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_task_report',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_time_log_report',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 1,
                    'name' => 'view_finance_report',

                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'display_name' => 'View Income Vs Expense Report',
                    'is_custom' => 1,
                    'name' => 'view_income_expense_report',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_leave_report',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_attendance_report',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_expense_report',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_lead_report',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_sales_report',
                ]
            ]
        ],
        [
            'module_name' => 'settings',
            'description' => 'User can manage settings',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_company_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_app_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_notification_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_currency_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_payment_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_finance_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_ticket_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_project_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_attendance_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_leave_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_custom_field_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_message_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_storage_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_language_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_lead_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_time_log_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_task_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_social_login_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_security_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_gdpr_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_theme_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_role_permission_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_module_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_google_calendar_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_contract_setting',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'manage_custom_link_setting',
                ],
            ]
        ],
        [
            'module_name' => 'dashboards',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_overview_dashboard',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_dashboard',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_client_dashboard',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_hr_dashboard',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_ticket_dashboard',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_finance_dashboard',
                ],
            ]
        ],
        [
            'module_name' => 'orders',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_order',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_order',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_order',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_order',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'view_project_orders',
                ],
            ]
        ],
        [
            'module_name' => 'knowledgebase',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_knowledgebase',
                ],
                [
                    'allowed_permissions' => '{"all":4,"added":1,"none":5}',
                    'is_custom' => 0,
                    'name' => 'view_knowledgebase',
                ],
                [
                    'allowed_permissions' => '{"all":4,"added":1,"none":5}',
                    'is_custom' => 0,
                    'name' => 'edit_knowledgebase',
                ],
                [
                    'allowed_permissions' => '{"all":4,"added":1,"none":5}',
                    'is_custom' => 0,
                    'name' => 'delete_knowledgebase',
                ],

            ]
        ],
        [
            'module_name' => 'bankaccount',
            'permissions' => [
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0,
                    'name' => 'add_bankaccount',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0,
                    'name' => 'view_bankaccount',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0,
                    'name' => 'edit_bankaccount',
                ],
                [
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
                    'is_custom' => 0,
                    'name' => 'delete_bankaccount',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_bank_transfer',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_bank_deposit',
                ],
                [
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 1,
                    'name' => 'add_bank_withdraw',
                ],
            ]
        ],
        [
            'module_name' => 'messages',
            'permissions' => []
        ],
        ...self::SUPERADMIN_MODULE_LIST
    ];

    // Will be used for roles and permission in saas
    const SUPERADMIN_MODULE_LIST = [];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'module_id')->where('is_custom', 0);
    }

    public function customPermissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'module_id')->where('is_custom', 1);
    }

    public function permissionsAll(): HasMany
    {
        return $this->hasMany(Permission::class, 'module_id');
    }

    public static function validateVersion($module)
    {
        if (app()->runningInConsole()) {
            return true;
        }

        $parentMinVersion = config(strtolower($module) . '.parent_min_version');

        if ($parentMinVersion >= File::get('version.txt')) {

            $module = \Nwidart\Modules\Facades\Module::findOrFail(strtolower($module));
            /* @phpstan-ignore-line */
            $module->disable();

            $message = 'To activate <strong>' . $module . '</strong> module, minimum version of <b>worksuite application</b> must be greater than equal to <b>' . $parentMinVersion . '</b> But your application version is <b>' . File::get('version.txt') . '</b>. Please upgrade the application to latest version';
            throw new \Exception($message);
        }
    }

    public static function disabledModuleArray()
    {
        $moduleInactive = [];
        $modulesAvailable = \Nwidart\Modules\Facades\Module::allDisabled();
        /* @phpstan-ignore-line */

        foreach ($modulesAvailable as $key => $module) {
            $moduleInactive[$key] = $module;
        }

        return $moduleInactive;
    }

}
