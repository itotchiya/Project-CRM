<?php

namespace App\Providers;

use App\Events\AppreciationEvent;
use App\Events\AttendanceReminderEvent;
use App\Events\AutoFollowUpReminderEvent;
use App\Events\AutoTaskReminderEvent;
use App\Events\BirthdayReminderEvent;
use App\Events\BulkShiftEvent;
use App\Events\ContractSignedEvent;
use App\Events\DailyTimeLogReportEvent;
use App\Events\DealEvent;
use App\Events\DiscussionEvent;
use App\Events\DiscussionReplyEvent;
use App\Events\EmployeeShiftChangeEvent;
use App\Events\EmployeeShiftScheduleEvent;
use App\Events\EstimateAcceptedEvent;
use App\Events\EstimateDeclinedEvent;
use App\Events\EventInviteEvent;
use App\Events\EventReminderEvent;
use App\Events\FileUploadEvent;
use App\Events\InvitationEmailEvent;
use App\Events\InvoicePaymentReceivedEvent;
use App\Events\InvoiceReminderAfterEvent;
use App\Events\InvoiceReminderEvent;
use App\Events\InvoiceUpdatedEvent;
use App\Events\LeadEvent;
use App\Events\LeaveEvent;
use App\Events\NewChatEvent;
use App\Events\NewContractEvent;
use App\Events\NewCreditNoteEvent;
use App\Events\NewEstimateEvent;
use App\Events\NewExpenseEvent;
use App\Events\NewExpenseRecurringEvent;
use App\Events\NewInvoiceEvent;
use App\Events\NewInvoiceRecurringEvent;
use App\Events\NewIssueEvent;
use App\Events\NewNoticeEvent;
use App\Events\NewOrderEvent;
use App\Events\NewPaymentEvent;
use App\Events\NewProductPurchaseEvent;
use App\Events\NewProjectEvent;
use App\Events\NewProjectMemberEvent;
use App\Events\NewProposalEvent;
use App\Events\NewUserEvent;
use App\Events\NewUserSlackEvent;
use App\Events\NewUserRegistrationViaInviteEvent;
use App\Events\OrderUpdatedEvent;
use App\Events\PaymentReminderEvent;
use App\Events\ProjectReminderEvent;
use App\Events\RemovalRequestAdminEvent;
use App\Events\RemovalRequestAdminLeadEvent;
use App\Events\RemovalRequestApprovedRejectLeadEvent;
use App\Events\RemovalRequestApprovedRejectUserEvent;
use App\Events\RemovalRequestApproveRejectEvent;
use App\Events\SubTaskCompletedEvent;
use App\Events\TaskCommentEvent;
use App\Events\TaskCommentMentionEvent;
use App\Events\TaskEvent;
use App\Events\TaskNoteEvent;
use App\Events\TaskReminderEvent;
use App\Events\TicketEvent;
use App\Events\TicketReplyEvent;
use App\Events\TicketRequesterEvent;
use App\Events\TimeTrackerReminderEvent;
use App\Events\DiscussionMentionEvent;
use App\Events\EventInviteMentionEvent;
use App\Events\EventStatusNoteEvent;
use App\Events\MailTicketReplyEvent;
use App\Events\MonthlyAttendanceEvent;
use App\Events\NewMentionChatEvent;
use App\Events\ProjectNoteEvent;
use App\Events\ProjectNoteMentionEvent;
use App\Events\TaskNoteMentionEvent;
use App\Events\TwoFactorCodeEvent;
use App\Events\DailyScheduleEvent;
use App\Events\EstimateRequestAcceptedEvent;
use App\Events\EstimateRequestRejectedEvent;
use App\Events\SubmitWeeklyTimesheet;
use App\Events\WeeklyTimesheetApprovedEvent;
use App\Events\WeeklyTimesheetDraftEvent;
use App\Listeners\AppreciationListener;
use App\Listeners\AttendanceReminderListener;
use App\Listeners\AutoFollowUpReminderListener;
use App\Listeners\AutoTaskReminderListener;
use App\Listeners\BirthdayReminderListener;
use App\Listeners\BulkShiftListener;
use App\Listeners\ContractSignedListener;
use App\Listeners\DailyTimeLogReportListener;
use App\Listeners\DealListener;
use App\Listeners\DiscussionListener;
use App\Listeners\DiscussionReplyListener;
use App\Listeners\EmployeeShiftChangeListener;
use App\Listeners\EmployeeShiftScheduleListener;
use App\Listeners\EstimateAcceptedListener;
use App\Listeners\EstimateDeclinedListener;
use App\Listeners\EventInviteListener;
use App\Listeners\EventReminderListener;
use App\Listeners\FileUploadListener;
use App\Listeners\InvitationEmailListener;
use App\Listeners\InvoicePaymentReceivedListener;
use App\Listeners\InvoiceReminderAfterListener;
use App\Listeners\InvoiceReminderListener;
use App\Listeners\InvoiceUpdatedListener;
use App\Listeners\LeadListener;
use App\Listeners\LeaveListener;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\NewChatListener;
use App\Listeners\NewContractListener;
use App\Listeners\NewCreditNoteListener;
use App\Listeners\NewEstimateListener;
use App\Listeners\NewExpenseListener;
use App\Listeners\NewExpenseRecurringListener;
use App\Listeners\NewInvoiceListener;
use App\Listeners\NewInvoiceRecurringListener;
use App\Listeners\NewIssueListener;
use App\Listeners\NewNoticeListener;
use App\Listeners\NewOrderListener;
use App\Listeners\NewPaymentListener;
use App\Listeners\NewProductPurchaseListener;
use App\Listeners\NewProjectListener;
use App\Listeners\NewProjectMemberListener;
use App\Listeners\NewProposalListener;
use App\Listeners\NewUserListener;
use App\Listeners\NewUserSlackListener;
use App\Listeners\NewUserRegistrationViaInviteListener;
use App\Listeners\OrderUpdatedListener;
use App\Listeners\PaymentReminderListener;
use App\Listeners\ProjectReminderListener;
use App\Listeners\RemovalRequestAdminLeadListener;
use App\Listeners\RemovalRequestAdminListener;
use App\Listeners\RemovalRequestApprovedRejectLeadListener;
use App\Listeners\RemovalRequestApprovedRejectListener;
use App\Listeners\RemovalRequestApprovedRejectUserListener;
use App\Listeners\SubTaskCompletedListener;
use App\Listeners\TaskCommentListener;
use App\Listeners\TaskCommentMentionListener;
use App\Listeners\TaskListener;
use App\Listeners\TaskNoteListener;
use App\Listeners\TaskReminderListener;
use App\Listeners\TicketListener;
use App\Listeners\TicketReplyListener;
use App\Listeners\TicketRequesterListener;
use App\Listeners\TimeTrackerReminderListener;
use App\Listeners\DiscussionMentionListener;
use App\Listeners\EventInviteMentionListener;
use App\Listeners\EventStatusNoteListener;
use App\Listeners\MailTicketReplyListener;
use App\Listeners\MonthlyAttendanceListener;
use App\Listeners\NewMentionChatListener;
use App\Listeners\ProjectNoteListener;
use App\Listeners\ProjectNoteMentionListener;
use App\Listeners\TaskNoteMentionListener;
use App\Listeners\TwoFactorCodeListener;
use App\Listeners\DailyScheduleListener;
use App\Listeners\EstimateRequestRejectedListener;
use App\Listeners\SubmitWeeklyTimesheetListener;
use App\Listeners\WeeklyTimesheetApprovedListener;
use App\Listeners\WeeklyTimesheetDraftListener;
use App\Models\AcceptEstimate;
use App\Models\Appreciation;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Award;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\ClientCategory;
use App\Models\ClientContact;
use App\Models\ClientDetails;
use App\Models\ClientDocument;
use App\Models\ClientNote;
use App\Models\ClientSubCategory;
use App\Models\ClientUserNote;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Contract;
use App\Models\ContractDiscussion;
use App\Models\ContractFile;
use App\Models\ContractRenew;
use App\Models\ContractSign;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\CreditNotes;
use App\Models\Currency;
use App\Models\CurrencyFormatSetting;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\CustomLinkSetting;
use App\Models\DashboardWidget;
use App\Models\Designation;
use App\Models\Discussion;
use App\Models\DiscussionCategory;
use App\Models\DiscussionFile;
use App\Models\DiscussionReply;
use App\Models\EmailNotificationSetting;
use App\Models\EmergencyContact;
use App\Models\EmployeeDetails;
use App\Models\EmployeeDocument;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftChangeRequest;
use App\Models\EmployeeShiftSchedule;
use App\Models\EmployeeSkill;
use App\Models\EmployeeTeam;
use App\Models\Estimate;
use App\Models\EstimateTemplate;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Expense;
use App\Models\ExpenseRecurring;
use App\Models\ExpensesCategory;
use App\Models\ExpensesCategoryRole;
use App\Models\FileStorage;
use App\Models\GlobalSetting;
use App\Models\GoogleCalendarModule;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\InvoiceFiles;
use App\Models\InvoiceSetting;
use App\Models\Issue;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseCategory;
use App\Models\LanguageSetting;
use App\Models\Deal;
use App\Models\LeadAgent;
use App\Models\LeadCategory;
use App\Models\LeadCustomForm;
use App\Models\DealFile;
use App\Models\DealFollowUp;
use App\Models\DealNote;
use App\Models\EstimateRequest;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\LeadPipeline;
use App\Models\LeadSource;
use App\Models\PipelineStage;
use App\Models\LeadStatus;
use App\Models\Leave;
use App\Models\LeaveFile;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use App\Models\LogTimeFor;
use App\Models\MessageSetting;
use App\Models\ModuleSetting;
use App\Models\Notice;
use App\Models\NoticeView;
use App\Models\OfflinePaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewayCredentials;
use App\Models\PermissionRole;
use App\Models\Pinned;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductFiles;
use App\Models\ProductSubCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectFile;
use App\Models\ProjectMember;
use App\Models\ProjectMilestone;
use App\Models\ProjectNote;
use App\Models\ProjectRating;
use App\Models\ProjectSetting;
use App\Models\ProjectStatusSetting;
use App\Models\ProjectTemplate;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Models\Proposal;
use App\Models\ProposalTemplate;
use App\Models\RecurringInvoice;
use App\Models\RemovalRequest;
use App\Models\Role;
use App\Models\Skill;
use App\Models\StickyNote;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\TaskCategory;
use App\Models\TaskComment;
use App\Models\TaskFile;
use App\Models\TaskLabelList;
use App\Models\TaskNote;
use App\Models\TaskSetting;
use App\Models\TaskUser;
use App\Models\Tax;
use App\Models\Team;
use App\Models\ThemeSetting;
use App\Models\Ticket;
use App\Models\TicketAgentGroups;
use App\Models\TicketFile;
use App\Models\TicketChannel;
use App\Models\TicketCustomForm;
use App\Models\TicketEmailSetting;
use App\Models\TicketGroup;
use App\Models\TicketReply;
use App\Models\TicketReplyTemplate;
use App\Models\TicketTag;
use App\Models\TicketTagList;
use App\Models\TicketType;
use App\Models\UnitType;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserChat;
use App\Models\UserchatFile;
use App\Models\UserInvitation;
use App\Models\UserLeadboardSetting;
use App\Models\UserPermission;
use App\Models\UserTaskboardSetting;
use App\Models\WeeklyTimesheet;
use App\Models\WeeklyTimesheetEntries;
use App\Observers\AcceptEstimateObserver;
use App\Observers\AppreciationObserver;
use App\Observers\AttendanceObserver;
use App\Observers\AttendanceSettingObserver;
use App\Observers\AwardObserver;
use App\Observers\BankAccountObserver;
use App\Observers\BankTransactionObserver;
use App\Observers\ClientCategoryObserver;
use App\Observers\ClientContactObserver;
use App\Observers\ClientDetailsObserver;
use App\Observers\ClientDocumentObserver;
use App\Observers\ClientNoteObserver;
use App\Observers\ClientSubCategoryObserver;
use App\Observers\ClientUserNotesObserver;
use App\Observers\CompanyAddressObserver;
use App\Observers\CompanyObserver;
use App\Observers\ContractDiscussionObserver;
use App\Observers\ContractFileObserver;
use App\Observers\ContractObserver;
use App\Observers\ContractRenewObserver;
use App\Observers\ContractSignObserver;
use App\Observers\ContractTemplateObserver;
use App\Observers\ContractTypeObserver;
use App\Observers\CreditNoteObserver;
use App\Observers\CurrencyFormatSettingObserver;
use App\Observers\CurrencyObserver;
use App\Observers\CustomFieldGroupObserver;
use App\Observers\CustomFieldsObserver;
use App\Observers\CustomLinkSettingObserver;
use App\Observers\DashboardWidgetObserver;
use App\Observers\DealNoteObserver;
use App\Observers\DealObserver;
use App\Observers\DesignationObserver;
use App\Observers\DiscussionCategoryObserver;
use App\Observers\DiscussionFileObserver;
use App\Observers\DiscussionObserver;
use App\Observers\DiscussionReplyObserver;
use App\Observers\EmailNotificationSettingObserver;
use App\Observers\EmergencyContactObserver;
use App\Observers\EmployeeDetailsObserver;
use App\Observers\EmployeeDocsObserver;
use App\Observers\EmployeeShiftChangeObserver;
use App\Observers\EmployeeShiftObserver;
use App\Observers\EmployeeShiftScheduleObserver;
use App\Observers\EmployeeSkillObserver;
use App\Observers\EmployeeTeamObserver;
use App\Observers\EstimateObserver;
use App\Observers\EstimateRequestObserver;
use App\Observers\EstimateTemplateObserver;
use App\Observers\EventAttendeeObserver;
use App\Observers\EventObserver;
use App\Observers\ExpenseObserver;
use App\Observers\ExpenseRecurringObserver;
use App\Observers\ExpensesCategoryObserver;
use App\Observers\ExpensesCategoryRoleObserver;
use App\Observers\FileStorageObserver;
use App\Observers\FileUploadObserver;
use App\Observers\GlobalSettingObserver;
use App\Observers\GoogleCalendarModuleObserver;
use App\Observers\HolidayObserver;
use App\Events\HolidayEvent;
use App\Events\NewEstimateRequestEvent;
use App\Events\PromotionAddedEvent;
use App\Events\ShiftRotationEvent;
use App\Listeners\EstimateRequestAcceptedListener;
use App\Listeners\HolidayListener;
use App\Listeners\NewEstimateRequestListener;
use App\Listeners\PromotionAddedListener;
use App\Listeners\ShiftRotationListener;
use App\Models\NoticeFile;
use App\Models\Promotion;
use App\Observers\InvoiceObserver;
use App\Observers\InvoiceRecurringObserver;
use App\Observers\InvoiceSettingObserver;
use App\Observers\IssueObserver;
use App\Observers\KnowledgeBaseCategoriesObserver;
use App\Observers\KnowledgeBaseObserver;
use App\Observers\LanguageSettingObserver;
use App\Observers\LeadAgentObserver;
use App\Observers\LeadCategoryObserver;
use App\Observers\LeadCustomFormObserver;
use App\Observers\LeadFileObserver;
use App\Observers\LeadFollowUpObserver;
use App\Observers\LeadNoteObserver;
use App\Observers\LeadObserver;
use App\Observers\LeadSourceObserver;
use App\Observers\LeadStatusObserver;
use App\Observers\LeaveFileObserver;
use App\Observers\LeaveObserver;
use App\Observers\LeaveSettingObserver;
use App\Observers\LeaveTypeObserver;
use App\Observers\LogTimeForObserver;
use App\Observers\MessageSettingObserver;
use App\Observers\ModuleSettingObserver;
use App\Observers\NewChatObserver;
use App\Observers\NoticeObserver;
use App\Observers\NoticeViewObserver;
use App\Observers\OfflinePaymentMethodObserver;
use App\Observers\OrderObserver;
use App\Observers\PaymentGatewayCredentialsObserver;
use App\Observers\PaymentObserver;
use App\Observers\PermissionRoleObserver;
use App\Observers\PinnedObserver;
use App\Observers\ProductCategoryObserver;
use App\Observers\ProductFileObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductSubCategoryObserver;
use App\Observers\ProjectCategoryObserver;
use App\Observers\ProjectMemberObserver;
use App\Observers\ProjectMilestoneObserver;
use App\Observers\ProjectNoteObserver;
use App\Observers\ProjectObserver;
use App\Observers\ProjectRatingObserver;
use App\Observers\ProjectSettingObserver;
use App\Observers\ProjectStatusSettingObserver;
use App\Observers\ProjectTemplateObserver;
use App\Observers\ProjectTimelogBreakObserver;
use App\Observers\ProjectTimelogObserver;
use App\Observers\ProposalObserver;
use App\Observers\ProposalTemplateObserver;
use App\Observers\RemovalRequestObserver;
use App\Observers\RoleObserver;
use App\Observers\SkillObserver;
use App\Observers\StickyNoteObserver;
use App\Observers\SubTaskObserver;
use App\Observers\TaskBoardColumnObserver;
use App\Observers\TaskCategoryObserver;
use App\Observers\TaskCommentObserver;
use App\Observers\TaskFileObserver;
use App\Observers\InvoiceFileObserver;
use App\Observers\LeadPipelineObserver;
use App\Observers\LeadStageObserver;
use App\Observers\NoticeFileObserver;
use App\Observers\PromotionObserver;
use App\Observers\TaskLabelListObserver;
use App\Observers\TaskNoteObserver;
use App\Observers\TaskObserver;
use App\Observers\TaskSettingObserver;
use App\Observers\TaskUserObserver;
use App\Observers\TaxObserver;
use App\Observers\TeamObserver;
use App\Observers\ThemeSettingObserver;
use App\Observers\TicketAgentGroupsObserver;
use App\Observers\TicketChannelObserver;
use App\Observers\TicketCustomFormObserver;
use App\Observers\TicketEmailSettingObserver;
use App\Observers\TicketGroupObserver;
use App\Observers\TicketObserver;
use App\Observers\TicketReplyObserver;
use App\Observers\TicketFileObserver;
use App\Observers\TicketReplyTemplateObserver;
use App\Observers\TicketTagListObserver;
use App\Observers\TicketTagObserver;
use App\Observers\TicketTypeObserver;
use App\Observers\UnitTypeObserver;
use App\Observers\UniversalSearchObserver;
use App\Observers\UserActivityObserver;
use App\Observers\UserchatFileObserver;
use App\Observers\UserInvitationObserver;
use App\Observers\UserLeadboardSettingObserver;
use App\Observers\UserObserver;
use App\Observers\UserPermissionObserver;
use App\Observers\UserTaskboardSettingObserver;
use App\Observers\WeeklyTimeSheetObserver;
use App\Observers\WeeklyTimesheetEntriesObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [LogSuccessfulLogin::class],
        SubTaskCompletedEvent::class => [SubTaskCompletedListener::class],
        NewUserEvent::class => [NewUserListener::class],
        NewUserSlackEvent::class => [NewUserSlackListener::class],
        NewContractEvent::class => [NewContractListener::class],
        NewEstimateEvent::class => [NewEstimateListener::class],
        NewExpenseEvent::class => [NewExpenseListener::class],
        FileUploadEvent::class => [FileUploadListener::class],
        NewInvoiceEvent::class => [NewInvoiceListener::class],
        InvoiceUpdatedEvent::class => [InvoiceUpdatedListener::class],
        InvoicePaymentReceivedEvent::class => [InvoicePaymentReceivedListener::class],
        NewIssueEvent::class => [NewIssueListener::class],
        LeaveEvent::class => [LeaveListener::class],
        NewChatEvent::class => [NewChatListener::class],
        NewMentionChatEvent::class => [NewMentionChatListener::class],
        NewNoticeEvent::class => [NewNoticeListener::class],
        NewPaymentEvent::class => [NewPaymentListener::class],
        NewProjectMemberEvent::class => [NewProjectMemberListener::class],
        ProjectNoteMentionEvent::class => [ProjectNoteMentionListener::class],
        ProjectNoteEvent::class => [ProjectNoteListener::class],
        RemovalRequestAdminLeadEvent::class => [RemovalRequestAdminLeadListener::class],
        RemovalRequestAdminEvent::class => [RemovalRequestAdminListener::class],
        RemovalRequestApprovedRejectLeadEvent::class => [RemovalRequestApprovedRejectLeadListener::class],
        RemovalRequestApprovedRejectUserEvent::class => [RemovalRequestApprovedRejectUserListener::class],
        TaskCommentEvent::class => [TaskCommentListener::class],
        TaskCommentMentionEvent::class => [TaskCommentMentionListener::class],
        TaskNoteEvent::class => [TaskNoteListener::class],
        TaskNoteMentionEvent::class => [TaskNoteMentionListener::class],
        TaskEvent::class => [TaskListener::class],
        TicketEvent::class => [TicketListener::class],
        TicketReplyEvent::class => [TicketReplyListener::class],
        EventInviteEvent::class => [EventInviteListener::class],
        ProjectReminderEvent::class => [ProjectReminderListener::class],
        PaymentReminderEvent::class => [PaymentReminderListener::class],
        AutoTaskReminderEvent::class => [AutoTaskReminderListener::class],
        TaskReminderEvent::class => [TaskReminderListener::class],
        EventReminderEvent::class => [EventReminderListener::class],
        LeadEvent::class => [LeadListener::class],
        DiscussionReplyEvent::class => [DiscussionReplyListener::class],
        DiscussionEvent::class => [DiscussionListener::class],
        DiscussionMentionEvent::class => [DiscussionMentionListener::class],
        EstimateDeclinedEvent::class => [EstimateDeclinedListener::class],
        NewProposalEvent::class => [NewProposalListener::class],
        TicketRequesterEvent::class => [TicketRequesterListener::class],
        RemovalRequestApproveRejectEvent::class => [RemovalRequestApprovedRejectListener::class],
        NewExpenseRecurringEvent::class => [NewExpenseRecurringListener::class],
        NewInvoiceRecurringEvent::class => [NewInvoiceRecurringListener::class],
        NewCreditNoteEvent::class => [NewCreditNoteListener::class],
        NewProjectEvent::class => [NewProjectListener::class],
        NewProductPurchaseEvent::class => [NewProductPurchaseListener::class],
        InvitationEmailEvent::class => [InvitationEmailListener::class],
        InvoiceReminderEvent::class => [InvoiceReminderListener::class],
        InvoiceReminderAfterEvent::class => [InvoiceReminderAfterListener::class],
        AttendanceReminderEvent::class => [AttendanceReminderListener::class],
        NewOrderEvent::class => [NewOrderListener::class],
        OrderUpdatedEvent::class => [OrderUpdatedListener::class],
        NewUserRegistrationViaInviteEvent::class => [NewUserRegistrationViaInviteListener::class],
        AutoFollowUpReminderEvent::class => [AutoFollowUpReminderListener::class],
        ContractSignedEvent::class => [ContractSignedListener::class],
        EmployeeShiftScheduleEvent::class => [EmployeeShiftScheduleListener::class],
        EmployeeShiftChangeEvent::class => [EmployeeShiftChangeListener::class],
        TwoFactorCodeEvent::class => [TwoFactorCodeListener::class],
        BirthdayReminderEvent::class => [BirthdayReminderListener::class],
        AppreciationEvent::class => [AppreciationListener::class],
        TimeTrackerReminderEvent::class => [TimeTrackerReminderListener::class],
        HolidayEvent::class => [HolidayListener::class],
        EstimateAcceptedEvent::class => [EstimateAcceptedListener::class],
        EventInviteMentionEvent::class => [EventInviteMentionListener::class],
        DealEvent::class => [DealListener::class],
        EventStatusNoteEvent::class => [EventStatusNoteListener::class],
        BulkShiftEvent::class => [BulkShiftListener::class],
        MonthlyAttendanceEvent::class => [MonthlyAttendanceListener::class],
        DailyTimeLogReportEvent::class => [DailyTimeLogReportListener::class],
        MailTicketReplyEvent::class => [MailTicketReplyListener::class],
        DailyScheduleEvent::class => [DailyScheduleListener::class],
        EstimateRequestRejectedEvent::class => [EstimateRequestRejectedListener::class],
        EstimateRequestAcceptedEvent::class => [EstimateRequestAcceptedListener::class],
        NewEstimateRequestEvent::class => [NewEstimateRequestListener::class],
        ShiftRotationEvent::class => [ShiftRotationListener::class],
        PromotionAddedEvent::class => [PromotionAddedListener::class],
        SubmitWeeklyTimesheet::class => [SubmitWeeklyTimesheetListener::class],
        WeeklyTimesheetApprovedEvent::class => [WeeklyTimesheetApprovedListener::class],
        WeeklyTimesheetDraftEvent::class => [WeeklyTimesheetDraftListener::class],
    ];

    protected $observers = [
        Attendance::class => [AttendanceObserver::class],
        ClientContact::class => [ClientContactObserver::class],
        ClientDetails::class => [ClientDetailsObserver::class],
        ClientDocument::class => [ClientDocumentObserver::class],
        ClientNote::class => [ClientNoteObserver::class],
        ClientUserNote::class => [ClientUserNotesObserver::class],
        Contract::class => [ContractObserver::class],
        ContractDiscussion::class => [ContractDiscussionObserver::class],
        ContractFile::class => [ContractFileObserver::class],
        ContractRenew::class => [ContractRenewObserver::class],
        CreditNotes::class => [CreditNoteObserver::class],
        CustomField::class => [CustomFieldsObserver::class],
        Discussion::class => [DiscussionObserver::class],
        DiscussionCategory::class => [DiscussionCategoryObserver::class],
        DiscussionReply::class => [DiscussionReplyObserver::class],
        EmployeeDetails::class => [EmployeeDetailsObserver::class],
        EmployeeDocument::class => [EmployeeDocsObserver::class],
        EmergencyContact::class => [EmergencyContactObserver::class],
        EmployeeShift::class => [EmployeeShiftObserver::class],
        EmployeeShiftChangeRequest::class => [EmployeeShiftChangeObserver::class],
        EmployeeShiftSchedule::class => [EmployeeShiftScheduleObserver::class],
        Estimate::class => [EstimateObserver::class],
        Event::class => [EventObserver::class],
        Expense::class => [ExpenseObserver::class],
        ExpenseRecurring::class => [ExpenseRecurringObserver::class],
        Holiday::class => [HolidayObserver::class],
        Invoice::class => [InvoiceObserver::class],
        InvoiceSetting::class => [InvoiceSettingObserver::class],
        Issue::class => [IssueObserver::class],
        Deal::class => [DealObserver::class],
        LeadAgent::class => [LeadAgentObserver::class],
        LeadCategory::class => [LeadCategoryObserver::class],
        LeadCustomForm::class => [LeadCustomFormObserver::class],
        DealFile::class => [LeadFileObserver::class],
        DealFollowUp::class => [LeadFollowUpObserver::class],
        LeadNote::class => [LeadNoteObserver::class],
        DealNote::class => [DealNoteObserver::class],
        LeadSource::class => [LeadSourceObserver::class],
        LeadStatus::class => [LeadStatusObserver::class],
        Leave::class => [LeaveObserver::class],
        LeaveSetting::class => [LeaveSettingObserver::class],
        LeaveType::class => [LeaveTypeObserver::class],
        Notice::class => [NoticeObserver::class],
        NoticeFile::class => [NoticeFileObserver::class],
        Order::class => [OrderObserver::class],
        Payment::class => [PaymentObserver::class],
        PermissionRole::class => [PermissionRoleObserver::class],
        Role::class => [RoleObserver::class],
        Pinned::class => [PinnedObserver::class],
        Product::class => [ProductObserver::class],
        ProductFiles::class => [ProductFileObserver::class],
        Project::class => [ProjectObserver::class],
        ProjectCategory::class => [ProjectCategoryObserver::class],
        ProjectFile::class => [FileUploadObserver::class],
        ProjectMember::class => [ProjectMemberObserver::class],
        ProjectMilestone::class => [ProjectMilestoneObserver::class],
        ProjectNote::class => [ProjectNoteObserver::class],
        ProjectRating::class => [ProjectRatingObserver::class],
        ProjectTimeLog::class => [ProjectTimelogObserver::class],
        ProjectTimeLogBreak::class => [ProjectTimelogBreakObserver::class],
        Proposal::class => [ProposalObserver::class],
        RecurringInvoice::class => [InvoiceRecurringObserver::class],
        RemovalRequest::class => [RemovalRequestObserver::class],
        // RemovalRequestDeal::class => [RemovalRequestLeadObserver::class],
        SubTask::class => [SubTaskObserver::class],
        Task::class => [TaskObserver::class],
        TaskboardColumn::class => [TaskBoardColumnObserver::class],
        TaskCategory::class => [TaskCategoryObserver::class],
        TaskComment::class => [TaskCommentObserver::class],
        TaskFile::class => [TaskFileObserver::class],
        InvoiceFiles::class => [InvoiceFileObserver::class],
        TaskLabelList::class => [TaskLabelListObserver::class],
        TaskNote::class => [TaskNoteObserver::class],
        TaskUser::class => [TaskUserObserver::class],
        Ticket::class => [TicketObserver::class],
        TicketEmailSetting::class => [TicketEmailSettingObserver::class],
        TicketReply::class => [TicketReplyObserver::class],
        TicketFile::class => [TicketFileObserver::class],
        TicketReplyTemplate::class => [TicketReplyTemplateObserver::class],
        User::class => [UserObserver::class],
        UserChat::class => [NewChatObserver::class],
        UserInvitation::class => [UserInvitationObserver::class],
        CompanyAddress::class => [CompanyAddressObserver::class],
        ContractType::class => [ContractTypeObserver::class],
        DashboardWidget::class => [DashboardWidgetObserver::class],
        Designation::class => [DesignationObserver::class],
        EmailNotificationSetting::class => [EmailNotificationSettingObserver::class],
        EventAttendee::class => [EventAttendeeObserver::class],
        GoogleCalendarModule::class => [GoogleCalendarModuleObserver::class],
        LogTimeFor::class => [LogTimeForObserver::class],
        ModuleSetting::class => [ModuleSettingObserver::class],
        ProjectSetting::class => [ProjectSettingObserver::class],
        Tax::class => [TaxObserver::class],
        Team::class => [TeamObserver::class],
        ThemeSetting::class => [ThemeSettingObserver::class],
        TicketGroup::class => [TicketGroupObserver::class],
        TicketAgentGroups::class => [TicketAgentGroupsObserver::class],
        TicketChannel::class => [TicketChannelObserver::class],
        TicketCustomForm::class => [TicketCustomFormObserver::class],
        TicketType::class => [TicketTypeObserver::class],
        UniversalSearch::class => [UniversalSearchObserver::class],
        AttendanceSetting::class => [AttendanceSettingObserver::class],
        MessageSetting::class => [MessageSettingObserver::class],
        Currency::class => [CurrencyObserver::class],
        KnowledgeBaseCategory::class => [KnowledgeBaseCategoriesObserver::class],
        KnowledgeBase::class => [KnowledgeBaseObserver::class],
        Company::class => [CompanyObserver::class],
        StickyNote::class => [StickyNoteObserver::class],
        Skill::class => [SkillObserver::class],
        ProjectStatusSetting::class => [ProjectStatusSettingObserver::class],
        UserPermission::class => [UserPermissionObserver::class],
        ProposalTemplate::class => [ProposalTemplateObserver::class],
        BankAccount::class => [BankAccountObserver::class],
        BankTransaction::class => [BankTransactionObserver::class],
        Award::class => [AwardObserver::class],
        Appreciation::class => [AppreciationObserver::class],
        ProjectTemplate::class => [ProjectTemplateObserver::class],
        ExpensesCategory::class => [ExpensesCategoryObserver::class],
        ExpensesCategoryRole::class => [ExpensesCategoryRoleObserver::class],
        DiscussionFile::class => [DiscussionFileObserver::class],
        TaskSetting::class => [TaskSettingObserver::class],
        OfflinePaymentMethod::class => [OfflinePaymentMethodObserver::class],
        CustomFieldGroup::class => [CustomFieldGroupObserver::class],
        EmployeeSkill::class => [EmployeeSkillObserver::class],
        EmployeeTeam::class => [EmployeeTeamObserver::class],
        NoticeView::class => [NoticeViewObserver::class],
        PaymentGatewayCredentials::class => [PaymentGatewayCredentialsObserver::class],
        TicketTag::class => [TicketTagObserver::class],
        TicketTagList::class => [TicketTagListObserver::class],
        UserchatFile::class => [UserchatFileObserver::class],
        UserActivity::class => [UserActivityObserver::class],
        FileStorage::class => [FileStorageObserver::class],
        UserTaskboardSetting::class => [UserTaskboardSettingObserver::class],
        UserLeadboardSetting::class => [UserLeadboardSettingObserver::class],
        ClientCategory::class => [ClientCategoryObserver::class],
        ClientSubCategory::class => [ClientSubCategoryObserver::class],
        ProductCategory::class => [ProductCategoryObserver::class],
        ProductSubCategory::class => [ProductSubCategoryObserver::class],
        AcceptEstimate::class => [AcceptEstimateObserver::class],
        ContractSign::class => [ContractSignObserver::class],
        ContractTemplate::class => [ContractTemplateObserver::class],
        LeaveFile::class => [LeaveFileObserver::class],
        CurrencyFormatSetting::class => [CurrencyFormatSettingObserver::class],
        EstimateTemplate::class => [EstimateTemplateObserver::class],
        UnitType::class => [UnitTypeObserver::class],
        LanguageSetting::class => [LanguageSettingObserver::class],
        GlobalSetting::class => [GlobalSettingObserver::class],
        CustomLinkSetting::class => [CustomLinkSettingObserver::class],
        PipelineStage::class => [LeadStageObserver::class],
        LeadPipeline::class => [LeadPipelineObserver::class],
        Lead::class => [LeadObserver::class],
        EstimateRequest::class => [EstimateRequestObserver::class],
        Promotion::class => [PromotionObserver::class],
        WeeklyTimesheet::class => [WeeklyTimeSheetObserver::class],
        WeeklyTimesheetEntries::class => [WeeklyTimesheetEntriesObserver::class],

    ];

}
