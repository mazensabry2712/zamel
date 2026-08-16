<?php

namespace App\Actions\Profile;

use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateProfile
{
    public function execute(Profile $profile, array $data): Profile
    {
        return DB::transaction(function () use ($profile, $data): Profile {
            if (array_key_exists('avatar', $data) && $data['avatar'] instanceof UploadedFile) {
                if ($profile->avatar) {
                    Storage::disk('public')->delete($profile->avatar);
                }

                $data['avatar'] = $data['avatar']->store('avatars', 'public');
            }

            $profile->update($data);

            return $profile->fresh();
        });
    }
}
