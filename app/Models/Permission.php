<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Trebol\Entrust\EntrustPermission;

/**
 * App\Models\Permission
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property int $module_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_custom
 * @property-read \App\Models\Module $module
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereIsCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereUpdatedAt($value)
 * @property string|null $allowed_permissions
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereAllowedPermissions($value)
 * @mixin \Eloquent
 */
class Permission extends EntrustPermission
{

    const ALL_NONE = '{"all":4, "none":5}';

    const ALL_ADDED_NONE = '{"all":4, "added":1, "none":5}';

    const ALL_4_ADDED_1_NONE_5 = '{"all":4,"added":1, "none":5}';

    const ALL_4_OWNED_2_NONE_5 = '{"all":4, "owned":2, "none":5}';

    const ALL_4_ADDED_1_OWNED_2_NONE_5 = '{"all":4, "added":1, "owned":2, "none":5}';

    const ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5 = '{"all":4, "added":1, "owned":2,"both":3, "none":5}';

    protected $fillable = ['name', 'display_name', 'description', 'module_id', 'is_custom', 'allowed_permissions'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

}
