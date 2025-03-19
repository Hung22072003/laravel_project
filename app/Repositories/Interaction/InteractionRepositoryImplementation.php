<?php

namespace App\Repositories\Interaction;

use App\Models\Interaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InteractionRepositoryImplementation implements InteractionRepositoryInterface
{
    public function createOrUpdateInteraction($postPlatformId, $data) {
        Log::info('Vào được Repo đây rồi');
        $interaction = Interaction::where('post_platform_id', $postPlatformId)->whereDate('day', Carbon::today())->first();
        if($interaction) {
            DB::transaction(function () use ($interaction, $data) {
                $interaction->update([
                    Interaction::NUMBER_OF_LIKES => $data["likes"],
                    Interaction::NUMBER_OF_SHARES => $data["shares"],
                    Interaction::NUMBER_OF_COMMENTS => $data["comments"]
                ]);
            });
        } else {
            Interaction::create([
                Interaction::ID => Str::uuid(),
                Interaction::POST_PLATFORM_ID => $postPlatformId,
                Interaction::NUMBER_OF_LIKES => $data["likes"],
                Interaction::NUMBER_OF_SHARES => $data["shares"],
                Interaction::NUMBER_OF_COMMENTS => $data["comments"],
                Interaction::DAY => Carbon::today(),
            ]);
        }
    }
}
