<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\TicketEmailSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $mail_username
 * @property string|null $mail_password
 * @property string|null $mail_from_name
 * @property string|null $mail_from_email
 * @property string|null $imap_host
 * @property string|null $imap_port
 * @property string|null $imap_encryption
 * @property int $status
 * @property int $verified
 * @property int $sync_interval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereImapEncryption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereImapHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereImapPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailFromEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereSyncInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereVerified($value)
 * @property-read \App\Models\Company|null $company
 * @mixin \Eloquent
 */
class TicketEmailSetting extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];

}
