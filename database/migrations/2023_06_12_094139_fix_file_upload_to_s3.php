<?php

use App\Models\User;
use App\Helper\Files;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // WithoutGlobalScopes
        $files = [
            [
                'model' => Company::class,
                'columns' => [
                    [
                        'name' => 'logo',
                        'path' => 'app-logo',
                    ],
                    [
                        'name' => 'light_logo',
                        'path' => 'app-logo',
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
                'model' => User::class,
                'columns' => [
                    [
                        'name' => 'image',
                        'path' => 'avatar',
                    ],
                ],
            ]
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
