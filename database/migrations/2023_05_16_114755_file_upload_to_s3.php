<?php

use App\Models\GlobalSetting;
use App\Models\User;
use App\Helper\Files;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Passport;
use App\Models\VisaDetail;
use App\Models\Appreciation;
use App\Models\ContractSign;
use App\Models\ProposalSign;
use App\Models\SlackSetting;
use App\Models\ClientDetails;
use App\Models\AcceptEstimate;
use App\Models\InvoiceSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $files = [
            [
                'model' => Company::class,
                'columns' => [
                    [
                        'name' => 'logo',
                        'path' => GlobalSetting::APP_LOGO_PATH,
                    ],
                    [
                        'name' => 'light_logo',
                        'path' => GlobalSetting::APP_LOGO_PATH,
                    ],
                    [
                        'name' => 'login_background',
                        'path' => 'login-background',
                    ],
                    [
                        'name' => 'favicon',
                        'path' => 'favicon',
                    ],
                ],
            ],
            [
                'model' => Appreciation::class,
                'columns' => [
                    [
                        'name' => 'image',
                        'path' => 'appreciation',
                    ],
                ],
            ],
            [
                'model' => User::class,
                'columns' => [
                    [
                        'name' => 'image',
                        'path' => 'avatar',
                    ],
                ],
            ],
            [
                'model' => ClientDetails::class,
                'columns' => [
                    [
                        'name' => 'company_logo',
                        'path' => 'client-logo',
                    ],
                ],
            ],
            [
                'model' => Contract::class,
                'columns' => [
                    [
                        'name' => 'company_sign',
                        'path' => 'contract/sign',
                    ],
                ],
            ],
            [
                'model' => VisaDetail::class,
                'columns' => [
                    [
                        'name' => 'file',
                        'path' => VisaDetail::FILE_PATH,
                    ],
                ],
            ],
            [
                'model' => AcceptEstimate::class,
                'columns' => [
                    [
                        'name' => 'signature',
                        'path' => 'estimate/accept',
                    ],
                ],
            ],
            [
                'model' => ContractSign::class,
                'columns' => [
                    [
                        'name' => 'signature',
                        'path' => 'contract/sign',
                    ],
                ],
            ],
            [
                'model' => ProposalSign::class,
                'columns' => [
                    [
                        'name' => 'signature',
                        'path' => 'proposal/sign',
                    ],
                ],
            ],
            [
                'model' => InvoiceSetting::class,
                'columns' => [
                    [
                        'name' => 'logo',
                        'path' => GlobalSetting::APP_LOGO_PATH,
                    ],
                    [
                        'name' => 'authorised_signatory_signature',
                        'path' => GlobalSetting::APP_LOGO_PATH,
                    ],
                ],
            ],
            [
                'model' => Passport::class,
                'columns' => [
                    [
                        'name' => 'file',
                        'path' => Passport::FILE_PATH,
                    ],
                ],
            ],
            [
                'model' => SlackSetting::class,
                'columns' => [
                    [
                        'name' => 'slack_logo',
                        'path' => 'slack-logo',
                    ],
                ],
            ],
        ];

        foreach ($files as $file) {
            $model = $file['model'];
            $columns = $file['columns'];

            Files::fixLocalUploadFiles($model, $columns);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
