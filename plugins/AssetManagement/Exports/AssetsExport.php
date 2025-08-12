<?php

namespace Plugins\AssetManagement\Exports;

use Plugins\AssetManagement\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        return Asset::query()->with('media');
    }

    public function map($asset): array
    {

        $general_settings = get_settings('general_settings');
        $currency_symbol = $general_settings['currency_symbol'] ?? '₹';

        return [
            $asset->id,
            $asset->name,
            $asset->asset_tag,
            $asset->category->name,
            $asset->assignedUser ? $asset->assignedUser->first_name . ' ' . $asset->assignedUser->last_name : null,
            $asset->status,
            $asset->description,
            $asset->purchase_cost ? $currency_symbol . " " . $asset->purchase_cost : '-',
            $asset->purchase_date,
            $asset->created_at,
            $asset->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Asset Tag',
            'Category Name',
            'Assigned To',
            'Status',
            'Description',
            'Purchase Cost',
            'Purchase Date',
            'Created At',
            'Updated At',
        ];
    }
}
