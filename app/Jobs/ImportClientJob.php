<?php

namespace App\Jobs;

use App\Models\Role;
use App\Models\User;
use App\Models\ClientDetails;
use App\Models\Country;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Traits\ExcelImportable;
use Illuminate\Support\Facades\DB;
use App\Traits\UniversalSearchTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportClientJob implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;
    private $columns;
    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->isColumnExists('name')) {

            $user = null;

            if ($this->isColumnExists('email') && $this->isEmailValid($this->getColumnValue('email'))) {
                $user = User::where('email', $this->getColumnValue('email'))->first();
            }

            if ($user) {
                $this->failJobWithMessage(__('messages.duplicateEntryForEmail') . $this->getColumnValue('email'));
            }
            else {
                DB::beginTransaction();
                try {

                    $countryID = $this->isColumnExists('country_id') ? Country::where('name', $this->getColumnValue('country_id'))->first()->id : null;

                    $user = new User();
                    $user->company_id = $this->company?->id;
                    $user->name = $this->getColumnValue('name');
                    $user->email = $this->isColumnExists('email') && $this->isEmailValid($this->getColumnValue('email')) ? $this->getColumnValue('email') : null;
                    $user->password = bcrypt(123456);
                    $user->mobile = $this->isColumnExists('mobile') ? $this->getColumnValue('mobile') : null;
                    $user->gender = $this->isColumnExists('gender') ? strtolower($this->getColumnValue('gender')) : null;
                    $user->country_id = $countryID;
                    $user->save();

                    if ($user->id) {
                        $clientDetails = new ClientDetails();
                        $clientDetails->company_id = $this->company?->id;
                        $clientDetails->user_id = $user->id;
                        $clientDetails->company_name = $this->isColumnExists('company_name') ? $this->getColumnValue('company_name') : null;
                        $clientDetails->address = $this->isColumnExists('address') ? $this->getColumnValue('address') : null;
                        $clientDetails->city = $this->isColumnExists('city') ? $this->getColumnValue('city') : null;
                        $clientDetails->state = $this->isColumnExists('state') ? $this->getColumnValue('state') : null;
                        $clientDetails->postal_code = $this->isColumnExists('postal_code') ? $this->getColumnValue('postal_code') : null;
                        $clientDetails->office = $this->isColumnExists('company_phone') ? $this->getColumnValue('company_phone') : null;
                        $clientDetails->website = $this->isColumnExists('company_website') ? $this->getColumnValue('company_website') : null;
                        $clientDetails->gst_number = $this->isColumnExists('gst_number') ? $this->getColumnValue('gst_number') : null;
                        $clientDetails->save();
                    }

                    $role = Role::where('name', 'client')->where('company_id', $this->company?->id)->select('id')->first();
                    $user->attachRole($role->id);

                    $user->assignUserRolePermission($role->id);

                    if (!is_null($user->email)) {
                        $this->logSearchEntry($user->id, $user->email, 'clients.show', 'client', $user->company_id);
                    }

                    if (!is_null($user->clientDetails->company_name)) {
                        $this->logSearchEntry($user->id, $user->clientDetails->company_name, 'clients.show', 'client', $user->company_id);
                    }

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->failJobWithMessage($e->getMessage());
                }
            }
        }
        else {
            $this->failJob(__('messages.invalidData'));
        }
    }

}
