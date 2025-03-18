<?php

namespace App\Models;

/**
 * App\Models\LanguageSetting
 *
 * @property int $id
 * @property string $language_code
 * @property string $language_name
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereLanguageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereLanguageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereUpdatedAt($value)
 * @property string|null $flag_code
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereFlagCode($value)
 * @property-read mixed $label
 * @property boolean $is_rtl
 * @mixin \Eloquent
 */
class LanguageSetting extends BaseModel
{

    protected $fillable = [
        'language_code',
        'language_name',
        'flag_code',
        'status',
        'is_rtl'
    ];

    const LANGUAGES_TRANS = [
        'en' => 'English',
        'ar' => 'عربي',
        'de' => 'Deutsch',
        'es' => 'Español',
        'et' => 'eesti keel',
        'fa' => 'فارسی',
        'fr' => 'français',
        'gr' => 'Ελληνικά',
        'it' => 'Italiano',
        'nl' => 'Nederlands',
        'pl' => 'Polski',
        'pt' => 'Português',
        'pt-br' => 'Português (Brasil)',
        'ro' => 'Română',
        'ru' => 'Русский',
        'tr' => 'Türk',
        'ja' => '日本語',
        'zh-CN' => '中国人',
        'zh-TW' => '中國人'
    ];

    const LANGUAGES = [
        [
            'language_code' => 'en',
            'flag_code' => 'en',
            'language_name' => 'English',
            'status' => 'enabled',
            'is_rtl' => false
        ],
        [
            'language_code' => 'ar',
            'flag_code' => 'sa',
            'language_name' => 'Arabic',
            'status' => 'disabled',
            'is_rtl' => true

        ],
        [
            'language_code' => 'bg',
            'flag_code' => 'bg',
            'language_name' => 'Bulgarian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'th',
            'flag_code' => 'th',
            'language_name' => 'Thai',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'sr',
            'flag_code' => 'rs',
            'language_name' => 'Serbian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'ka',
            'flag_code' => 'ge',
            'language_name' => 'Georgian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'de',
            'flag_code' => 'de',
            'language_name' => 'German',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'es',
            'flag_code' => 'es',
            'language_name' => 'Spanish',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'et',
            'flag_code' => 'et',
            'language_name' => 'Estonian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'fa',
            'flag_code' => 'ir',
            'language_name' => 'Farsi',
            'status' => 'disabled',
            'is_rtl' => true

        ],
        [
            'language_code' => 'fr',
            'flag_code' => 'fr',
            'language_name' => 'French',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'ja',
            'flag_code' => 'jp',
            'language_name' => 'Japanese',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'el',
            'flag_code' => 'gr',
            'language_name' => 'Greek',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'hi',
            'flag_code' => 'in',
            'language_name' => 'Hindi',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'id',
            'flag_code' => 'id',
            'language_name' => 'Indonesian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'it',
            'flag_code' => 'it',
            'language_name' => 'Italian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'nl',
            'flag_code' => 'nl',
            'language_name' => 'Dutch',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'pl',
            'flag_code' => 'pl',
            'language_name' => 'Polish',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'pt',
            'flag_code' => 'pt',
            'language_name' => 'Portuguese',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'pt-br',
            'flag_code' => 'br',
            'language_name' => 'Portuguese (Brazil)',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'ro',
            'flag_code' => 'ro',
            'language_name' => 'Romanian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'ru',
            'flag_code' => 'ru',
            'language_name' => 'Russian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'tr',
            'flag_code' => 'tr',
            'language_name' => 'Turkish',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'vi',
            'flag_code' => 'vn',
            'language_name' => 'Vietnamese',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'zh-CN',
            'flag_code' => 'cn',
            'language_name' => 'Chinese (S)',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'zh-TW',
            'flag_code' => 'cn',
            'language_name' => 'Chinese (T)',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'sq',
            'flag_code' => 'al',
            'language_name' => 'Albanian',
            'status' => 'disabled',
            'is_rtl' => false

        ],
        [
            'language_code' => 'ko',
            'flag_code' => 'ko',
            'language_name' => 'korean',
            'status' => 'disabled',
            'is_rtl' => false

        ],
    ];

    public function getLabelAttribute()
    {
        $langCode = ($this->language_code == 'en') ? 'gb' : strtolower($this->flag_code);

        return ' <span  data-toggle="tooltip" data-original-title="' . $this->language_name . '" class="flag-icon flag-icon-' . $langCode . ' flag-icon-squared"></span>';
    }

}
