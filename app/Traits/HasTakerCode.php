<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasTakerCode
{
    /**
     * Get formatted taker code in the format: {group_code}-{taker_code}
     * 
     * @param int $takerId
     * @param int $groupId
     * @param string|null $groupCode
     * @return string
     */
    public static function getFormattedTakerCode(int $takerId, int $groupId, ?string $groupCode = null): string
    {
        // Get the taker code from group_taker pivot table
        $pivotData = DB::table('group_taker')
            ->where('group_id', $groupId)
            ->where('taker_id', $takerId)
            ->first();

        if (!$pivotData) {
            return 'No Code';
        }

        $takerCode = $pivotData->taker_code;
        
        // If taker_code is null, we need to generate it
        if (!$takerCode) {
            // Get the group to find the last_taker_code
            $group = DB::table('groups')->where('id', $groupId)->first();
            if (!$group) {
                return 'No Code';
            }

            $takerCode = str_pad($group->last_taker_code ?? 1, 3, '0', STR_PAD_LEFT);
            
            // Update the database with the generated code
            DB::table('group_taker')
                ->where('group_id', $groupId)
                ->where('taker_id', $takerId)
                ->update(['taker_code' => $takerCode]);
            
            // Increment the group's last_taker_code
            DB::table('groups')
                ->where('id', $groupId)
                ->increment('last_taker_code');
        }

        // Format as group_code-taker_code
        $groupCodePrefix = $groupCode ?? 'NULL';
        return $groupCodePrefix . '-' . $takerCode;
    }
}