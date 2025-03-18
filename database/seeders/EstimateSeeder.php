<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\UnitType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EstimateSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $client = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('client_details', 'users.id', '=', 'client_details.user_id')
            ->select('users.id')
            ->where('roles.name', 'client')
            ->where('users.company_id', $companyId)
            ->first();

        $unit = UnitType::select('id')->where('company_id', $companyId)->first();
        $currency = Currency::select('id')->where('company_id', $companyId)->first();
        $estimate = new Estimate();
        $estimate->company_id = $companyId;
        $estimate->estimate_number = '001';
        $estimate->client_id = $client->id;
        $estimate->valid_till = Carbon::parse((date('m')) . '/03/2022')->format('Y-m-d');
        $estimate->sub_total = 1200;
        $estimate->total = 1200;
        $estimate->currency_id = $currency->id;
        $estimate->note = null;
        $estimate->status = 'waiting';
        $estimate->save();

        $items = ['item 1', 'item 2'];
        $cost_per_item = ['500', '700'];
        $quantity = ['1', '1'];
        $amount = ['500', '700'];
        $type = ['item', 'item'];

        foreach ($items as $key => $item):
            if (!is_null($item)) {
                EstimateItem::create([
                    'estimate_id' => $estimate->id,
                    'item_name' => $item,
                    'type' => $type[$key],
                    'quantity' => $quantity[$key],
                    'unit_price' => $cost_per_item[$key],
                    'unit_id' => $unit->id,
                    'amount' => $amount[$key]
                ]);
            }

        endforeach;


        $estimate = new Estimate();
        $estimate->company_id = $companyId;
        $estimate->estimate_number = '002';
        $estimate->client_id = $client->id;
        $estimate->valid_till = Carbon::parse((date('m')) . '/10/2022')->format('Y-m-d');
        $estimate->sub_total = 4100;
        $estimate->total = 4100;
        $estimate->currency_id = $currency->id;
        $estimate->note = null;
        $estimate->status = 'waiting';
        $estimate->save();

        $items = ['item 3', 'item 4'];
        $cost_per_item = ['1200', '1700'];
        $quantity = ['2', '1'];
        $amount = ['2400', '1700'];
        $type = ['item', 'item'];

        foreach ($items as $key => $item):
            EstimateItem::create([
                'estimate_id' => $estimate->id,
                'item_name' => $item, 'type' => $type[$key],
                'quantity' => $quantity[$key],
                'unit_price' => $cost_per_item[$key],
                'unit_id' => $unit->id,
                'amount' => $amount[$key]
            ]);
        endforeach;
    }

}
