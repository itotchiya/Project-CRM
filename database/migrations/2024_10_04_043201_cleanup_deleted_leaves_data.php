<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Leave;
use App\Models\LeaveFile;
use App\Helper\Files;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingLeaveIds = Leave::pluck('id')->toArray();
        $folder = LeaveFile::FILE_PATH;
        $folderPath = public_path(Files::UPLOAD_FOLDER . '/' . $folder);

        if (File::exists($folderPath)) {

            $leaveFolders = File::directories($folderPath);
            foreach ($leaveFolders as $leaveFolder) {

                $leaveId = basename($leaveFolder);

                if (!in_array($leaveId, $existingLeaveIds)) {

                    File::deleteDirectory($leaveFolder);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
