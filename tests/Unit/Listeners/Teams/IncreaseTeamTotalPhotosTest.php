<?php

namespace Tests\Unit\Listeners\Teams;

use App\Events\ImageUploaded;
use App\Listeners\Teams\IncreaseTeamTotalPhotos;
use App\Models\Teams\Team;
use App\Models\User\User;
use Carbon\Carbon;
use Tests\TestCase;

class IncreaseTeamTotalPhotosTest extends TestCase
{
    /**
     * @param User $user
     * @return ImageUploaded
     */
    protected function getEvent(User $user): ImageUploaded
    {
        return new ImageUploaded(
            'city',
            'state',
            'country',
            'countryCode',
            'imageName',
            'teamName',
            $user->id,
            1,
            1,
            1,
            false,
            $user->active_team
        );
    }

    public function test_it_increases_team_total_photos()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Team $team */
        $team = Team::factory()->create();

        $user->teams()->attach($team);
        $user->active_team = $team->id;
        $user->save();

        $this->assertEquals(0, $user->fresh()->team->total_images);

        $oldUpdatedAt = $user->fresh()->team->updated_at;

        Carbon::setTestNow(now()->addMinute());

        /** @var IncreaseTeamTotalPhotos $listener */
        $listener = app(IncreaseTeamTotalPhotos::class);

        $listener->handle($this->getEvent($user));

        $user->refresh();
        $this->assertEquals(1, $user->team->total_images);
        $this->assertTrue($user->team->updated_at->greaterThan($oldUpdatedAt));
    }

    public function test_it_increases_users_contribution_to_team_total_photos()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Team $team */
        $team = Team::factory()->create();

        $user->teams()->attach($team);
        $user->active_team = $team->id;
        $user->save();

        $this->assertEquals(0, $user->fresh()->teams->first()->pivot->total_photos);

        $oldUpdatedAt = $user->fresh()->teams->first()->pivot->updated_at;

        Carbon::setTestNow(now()->addMinute());

        /** @var IncreaseTeamTotalPhotos $listener */
        $listener = app(IncreaseTeamTotalPhotos::class);

        $listener->handle($this->getEvent($user));

        $user->refresh();
        $this->assertEquals(1, $user->teams->first()->pivot->total_photos);
        $this->assertTrue(
            $user->teams->first()->pivot->updated_at->greaterThan($oldUpdatedAt)
        );
    }

}
