<?php

namespace App\Actions\Family;

use App\Models\Tribe;
use App\Models\User;
use Illuminate\Support\Carbon;

class SyncParentApprovedTribes
{
    /**
     * @param  list<int|string>  $tribeIds
     */
    public function sync(User $parent, array $tribeIds): void
    {
        $validIds = Tribe::query()
            ->whereIn('id', array_map('intval', $tribeIds))
            ->pluck('id')
            ->all();

        $syncData = collect($validIds)
            ->mapWithKeys(fn (int $id) => [$id => ['approved_at' => Carbon::now()]])
            ->all();

        $parent->approvedTribes()->sync($syncData);
    }
}
