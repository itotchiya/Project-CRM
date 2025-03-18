<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\HasMaskImage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\InvoiceSetting
 *
 * @property int $id
 * @property string $invoice_prefix
 * @property int $invoice_digit
 * @property string $estimate_prefix
 * @property int $estimate_digit
 * @property string $credit_note_prefix
 * @property int $credit_note_digit
 * @property string $template
 * @property int $due_after
 * @property string $invoice_terms
 * @property string|null $other_info
 * @property string|null $gst_number
 * @property string|null $show_gst
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $logo
 * @property int $send_reminder
 * @property string|null $locale
 * @property int $hsn_sac_code_show
 * @property-read mixed $icon
 * @property-read mixed $logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreditNoteDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreditNotePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereDueAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimatePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereGstNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereHsnSacCodeShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoiceDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoicePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoiceTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereUpdatedAt($value)
 * @property string|null $estimate_terms
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateTerms($value)
 * @property int $tax_calculation_msg
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereTaxCalculationMsg($value)
 * @property int|null $company_id
 * @property string|null $reminder
 * @property int $send_reminder_after
 * @property int $show_project
 * @property string|null $show_client_name
 * @property string|null $show_client_email
 * @property string|null $show_client_phone
 * @property string|null $show_client_company_address
 * @property string|null $show_client_company_name
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereSendReminderAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowProject($value)
 * @property string $invoice_number_separator
 * @property string $estimate_number_separator
 * @property string $credit_note_number_separator
 * @property string $contract_prefix
 * @property string $contract_number_separator
 * @property int $contract_digit
 * @property int $show_status
 * @property int $authorised_signatory
 * @property string|null $authorised_signatory_signature
 * @property-read mixed $authorised_signatory_signature_url
 * @property-read mixed $is_chinese_lang
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereAuthorisedSignatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereAuthorisedSignatorySignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereContractDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereContractNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereContractPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreditNoteNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoiceNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowStatus($value)
 * @property string $order_prefix
 * @property string $order_number_separator
 * @property int $order_digit
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOrderDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOrderNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOrderPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOtherInfo($value)
 * @mixin \Eloquent
 */
class InvoiceSetting extends BaseModel
{

    use HasCompany,HasMaskImage;

    protected $appends = ['logo_url','authorised_signatory_signature_url', 'is_chinese_lang'];

    public function getLogoUrlAttribute()
    {
        return (is_null($this->logo)) ? $this->company->logo_url : asset_url_local_s3('app-logo/' . $this->logo);
    }

    public function maskedLogoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return (is_null($this->logo)) ? $this->company->logo_url : $this->generateMaskedImageAppUrl('app-logo/' . $this->logo);
            },
        );

    }

    public function getAuthorisedSignatorySignatureUrlAttribute()
    {
        return (is_null($this->authorised_signatory_signature)) ? '' : asset_url_local_s3('app-logo/' . $this->authorised_signatory_signature);
    }

    public function maskedAuthorisedSignatorySignatureUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return (is_null($this->authorised_signatory_signature)) ? '' : $this->generateMaskedImageAppUrl('app-logo/' . $this->authorised_signatory_signature);
            },
        );

    }

    public function getIsChineseLangAttribute()
    {
        return in_array(strtolower($this->locale), ['zh-hk', 'zh-cn', 'zh-sg', 'zh-tw', 'cn']);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_type_shift');
    }

}
