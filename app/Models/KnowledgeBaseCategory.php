<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\KnowledgeBaseCategory
 *
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KnowledgeBase[] $knowledgebase
 * @property-read int|null $knowledgebase_count
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
class KnowledgeBaseCategory extends BaseModel
{

    use HasFactory, HasCompany;

    protected $table = 'knowledge_categories';

    public function knowledgebase(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class, 'category_id');
    }

}
