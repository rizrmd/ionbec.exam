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
        if (!$groupCode) {
            // If group code is null, generate and sync it to the database
            $group = DB::table('groups')->where('id', $groupId)->first();
            if ($group && $group->name) {
                // Extract meaningful parts from group name to create a code
                $nameParts = explode(' ', strtoupper($group->name));
                $generatedCode = '';
                foreach ($nameParts as $part) {
                    if (strlen($part) >= 3) {
                        $generatedCode .= substr($part, 0, 3);
                        if (strlen($generatedCode) >= 6) break;
                    }
                }
                $generatedCode = $generatedCode ?: 'GROUP' . $groupId;
                
                // Update the group code in the database
                DB::table('groups')
                    ->where('id', $groupId)
                    ->update(['code' => $generatedCode]);
                    
                $groupCodePrefix = $generatedCode;
            } else {
                $groupCodePrefix = 'GROUP' . $groupId;
                
                // Update the group code in the database
                DB::table('groups')
                    ->where('id', $groupId)
                    ->update(['code' => $groupCodePrefix]);
            }
        } else {
            $groupCodePrefix = $groupCode;
        }
        
        return $groupCodePrefix . '-' . $takerCode;
    }
}