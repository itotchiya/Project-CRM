<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Flag
 *
 * @property int $id
 * @property string|null $capital
 * @property string|null $code
 * @property string|null $continent
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|Flag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereCapital($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereContinent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereName($value)
 * @mixin \Eloquent
 */
class Flag extends BaseModel
{

    use HasFactory;
}
