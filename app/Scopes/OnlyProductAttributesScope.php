<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OnlyProductAttributesScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder
                ->selectRaw('
                    eav_attribute.attribute_id AS id,
                    IFNULL(value, eav_attribute.attribute_code) AS name,
                    used_for_sort_by AS sorting,
                    attribute_code AS code,
                    backend_type AS type,
                    frontend_input AS input,
                    is_filterable AS filter,
                    GREATEST(
                        is_searchable,
                        is_visible_on_front,
                        used_in_product_listing,
                        attribute_code IN ("'.implode('","', config('shop.default_flat_attributes')).'")
                    ) AS flat
                ')
                ->join('catalog_eav_attribute', 'eav_attribute.attribute_id', '=', 'catalog_eav_attribute.attribute_id')
                ->leftJoin('eav_attribute_label', function ($join) {
                    $join->on('eav_attribute.attribute_id', '=', 'eav_attribute_label.attribute_id')
                         ->where('store_id', config('shop.store'));
                })
                ->where('entity_type_id', 4); // catalog_product
    }
}