<?php

use App\Models\Company;
use App\Models\EmailNotificationSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('mention_users', function (Blueprint $table) {

                $table->id();
                $table->integer('task_comment_id')->unsigned()->nullable();

                $table->foreign('task_comment_id')->references('id')->on('task_comments')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->integer('task_note_id')->unsigned()->nullable();
                $table->foreign('task_note_id')->references('id')->on('task_notes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->integer('task_id')->unsigned()->nullable();
                $table->foreign('task_id')->references('id')->on('tasks')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->integer('project_id')->unsigned()->nullable();
                    $table->foreign('project_id')->references('id')->on('projects')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');

                $table->integer('project_note_id')->unsigned()->nullable();
                        $table->foreign('project_note_id')->references('id')->on('project_notes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');

                $table->integer('discussion_id')->unsigned()->nullable();
                            $table->foreign('discussion_id')->references('id')->on('discussions')
                                ->onDelete('cascade')
                                ->onUpdate('cascade');
                $table->integer('user_id')->unsigned()->nullable();
                            $table->foreign('user_id')->references('id')->on('users')
                                ->onDelete('cascade')
                                ->onUpdate('cascade');
                $table->integer('discussion_reply_id')->unsigned()->nullable();
                            $table->foreign('discussion_reply_id')->references('id')->on('discussion_replies')
                                ->onDelete('cascade')
                                ->onUpdate('cascade');
                $table->timestamps();

        });
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $settings = [

                [
                    'send_email' => 'yes',
                    'send_push' => 'no',
                    'company_id' => $company->id,
                    'send_slack' => 'no',
                    'setting_name' => 'Project Mention Notification',
                    'slug' => 'project-mention-notification',
                ],
                [
                    'send_email' => 'yes',
                    'send_push' => 'no',
                    'company_id' => $company->id,
                    'send_slack' => 'no',
                    'setting_name' => 'Task Mention Notification',
                    'slug' => 'task-mention-notification',
                ],


            ];
            EmailNotificationSetting::insert($settings);

        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mention_users');
    }

};
