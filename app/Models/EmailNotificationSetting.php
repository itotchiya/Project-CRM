<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\EmailNotificationSetting
 *
 * @property int $id
 * @property string $setting_name
 * @property string $send_email
 * @property string $send_slack
 * @property string $send_push
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $slug
 * @property-read mixed $icon
 * @method static Builder|EmailNotificationSetting newModelQuery()
 * @method static Builder|EmailNotificationSetting newQuery()
 * @method static Builder|EmailNotificationSetting query()
 * @method static Builder|EmailNotificationSetting whereCreatedAt($value)
 * @method static Builder|EmailNotificationSetting whereId($value)
 * @method static Builder|EmailNotificationSetting whereSendEmail($value)
 * @method static Builder|EmailNotificationSetting whereSendPush($value)
 * @method static Builder|EmailNotificationSetting whereSendSlack($value)
 * @method static Builder|EmailNotificationSetting whereSettingName($value)
 * @method static Builder|EmailNotificationSetting whereSlug($value)
 * @method static Builder|EmailNotificationSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read Company|null $company
 * @method static Builder|EmailNotificationSetting whereCompanyId($value)
 * @property string $send_twilio
 * @method static Builder|EmailNotificationSetting whereSendTwilio($value)
 * @mixin Eloquent
 */
class EmailNotificationSetting extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];

    const NOTIFICATIONS = [
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Expense/Added by Admin',
            'slug' => 'new-expenseadded-by-admin',

        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Expense/Added by Member',
            'slug' => 'new-expenseadded-by-member',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Expense Status Changed',
            'slug' => 'expense-status-changed',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Support Ticket Request',
            'slug' => 'new-support-ticket-request',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Leave Application',
            'slug' => 'new-leave-application',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Task Completed',
            'slug' => 'task-completed',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Task Status Changed',
            'slug' => 'task-status-updated',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Invoice Create/Update Notification',
            'slug' => 'invoice-createupdate-notification',
        ],
        [

            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Discussion Reply',
            'slug' => 'discussion-reply',

        ],
        [

            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Product Purchase Request',
            'slug' => 'new-product-purchase-request',

        ],
        [

            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Lead notification',
            'slug' => 'lead-notification',

        ],
        [

            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Order Create/Update Notification',
            'slug' => 'order-createupdate-notification',

        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'User Join via Invitation',
            'slug' => 'user-join-via-invitation',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Follow Up Reminder',
            'slug' => 'follow-up-reminder',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'User Registration/Added by Admin',
            'slug' => 'user-registrationadded-by-admin',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Employee Assign to Project',
            'slug' => 'employee-assign-to-project',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Notice Published',
            'slug' => 'new-notice-published',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'User Assign to Task',
            'slug' => 'user-assign-to-task',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'yes',
            'setting_name' => 'Birthday notification',
            'slug' => 'birthday-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Payment Notification',
            'slug' => 'payment-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Employee Appreciation',
            'slug' => 'appreciation-notification',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Holiday Notification',
            'slug' => 'holiday-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Estimate Notification',
            'slug' => 'estimate-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Event Notification',
            'slug' => 'event-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Message Notification',
            'slug' => 'message-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Project Mention Notification',
            'slug' => 'project-mention-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Task Mention',
            'slug' => 'task-mention-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Shift Assign Notification',
            'slug' => 'shift-assign-notification',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Daily Schedule Notification',
            'slug' => 'daily-schedule-notification',
        ]
    ];

    public static function userAssignTask()
    {
        return EmailNotificationSetting::where('slug', 'user-assign-to-task')->first();
    }

}
