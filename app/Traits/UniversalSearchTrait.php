<?php

namespace App\Traits;

use App\Models\UniversalSearch;

trait UniversalSearchTrait
{

    /**
     * @param int $searchableId
     * @param string $title
     * @param string $route
     * @param string $type
     * @throws RelatedResourceNotFoundException
     */
    public function logSearchEntry($searchableId, $title, $route, $type, $company_id = null)
    {
        $search = new UniversalSearch();
        $search->company_id = $company_id;
        $search->searchable_id = $searchableId;
        $search->title = $title;
        $search->route_name = $route;
        $search->module_type = $type;
        $search->save();
    }

}
