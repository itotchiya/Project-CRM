<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\DiscussionReply\StoreRequest;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Project;

class DiscussionReplyController extends AccountBaseController
{

    public function create()
    {
        $this->discussionId = request('id');
        return view('discussions.replies.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $reply = new DiscussionReply();
        $reply->user_id = $this->user->id;
        $reply->discussion_id = $request->discussion_id;
        $reply->body = trim_editor($request->description);
        $reply->save();

        $project = Project::findOrFail($reply->discussion->project_id);
        $userData = [];
        $usersData = $project->projectMembers;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);
        $html = view('discussions.replies.show', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'discussion_reply_id' => $reply->id]);
    }

    public function getReplies($id)
    {
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($id);

        $project = Project::findOrFail($this->discussion->project_id);
        $userData = [];
        $usersData = $project->projectMembers;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $html = view('discussions.replies.show', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    public function edit($id)
    {
        $this->reply = DiscussionReply::findOrFail($id); /* @phpstan-ignore-line */
        return view('discussions.replies.edit', $this->data);
    }

    public function update(StoreRequest $request, $id)
    {
        $reply = DiscussionReply::findOrFail($id);
        $reply->body = trim_editor($request->description);
        $reply->save();

        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);

        $userData = [];
        $usersData = $this->discussion->project->projectMembers;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $html = view('discussions.replies.show', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    public function destroy($id)
    {
        $reply = DiscussionReply::findOrFail($id);
        $reply->delete();

        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);

        $project = Project::findOrFail($this->discussion->project_id);
        $userData = [];
        $usersData = $project->projectMembers;

        foreach ($usersData as $user) {

            $url = route('employees.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->userData = $userData;
        $this->userRoles = user()->roles->pluck('name')->toArray();
        $this->discussion = Discussion::with('category', 'replies', 'replies.user', 'replies.files')->findOrFail($reply->discussion_id);
        $html = view('discussions.replies.show', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

}
