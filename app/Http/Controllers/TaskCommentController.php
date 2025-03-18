<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Tasks\StoreTaskComment;
use App\Models\TaskCommentEmoji;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.tasks';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('tasks', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * @param StoreTaskComment $request
     * @return mixed
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTaskComment $request)
    {

        $this->addPermission = user()->permission('add_task_comments');
        $task = Task::findOrFail($request->taskId);
        $taskUsers = $task->users->pluck('id')->toArray();

        abort_403(!(
            $this->addPermission == 'all'
            || ($this->addPermission == 'added' && $task->added_by == user()->id)
            || ($this->addPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->addPermission == 'added' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
        ));
        $comment = new TaskComment();
        $comment->comment = $request->comment;
        $comment->task_id = $request->taskId;
        $comment->user_id = user()->id;
        $comment->save();

        $this->comments = TaskComment::with('user', 'task', 'like', 'dislike', 'likeUsers', 'dislikeUsers')->where('task_id', $request->taskId)->orderByDesc('id')->get();

        $view = view('tasks.comments.show', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = TaskComment::findOrFail($id);
        $this->deletePermission = user()->permission('delete_task_comments');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added') && $comment->added_by == user()->id));

        $comment_task_id = $comment->task_id;
        $comment->delete();

        $this->comments = TaskComment::with('user', 'task', 'like', 'dislike', 'likeUsers', 'dislikeUsers')->where('task_id', $comment_task_id)->orderByDesc('id')->get();

        $view = view('tasks.comments.show', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->comment = TaskComment::with('user', 'task')->findOrFail($id);
        $taskuserData = [];
        $usersData = $this->comment->task->users;

        foreach ($usersData as $user) {
            $url = route('employees.show', [$user->id]);

            $taskuserData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];

        }

        $this->taskuserData = $taskuserData;

        $this->editPermission = user()->permission('edit_task_comments');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->comment->added_by == user()->id)));

        return view('tasks.comments.edit', $this->data);

    }

    public function update(StoreTaskComment $request, $id)
    {
        $comment = TaskComment::findOrFail($id);
        $this->editPermission = user()->permission('edit_task_comments');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $comment->added_by == user()->id)));

        $comment->comment = $request->comment;
        $comment->save();

        $this->comments = TaskComment::with('user', 'task', 'like', 'dislike', 'likeUsers', 'dislikeUsers')->where('task_id', $comment->task_id)->orderByDesc('id')->get();

        $view = view('tasks.comments.show', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function saveCommentLike(Request $request)
    {
        $currentEmoji = TaskCommentEmoji::where('comment_id', $request->commentId)->where('user_id', user()->id)->first();

        if(!is_null($currentEmoji)){
            if($currentEmoji->emoji_name != $request->emojiName)
            {
                $currentEmoji->delete();
                $this->emoji($request);
            }
            else {
                $currentEmoji->delete();
            }
        } else {
            $this->emoji($request);
        }

        $this->comment = TaskComment::with('user', 'like', 'dislike', 'likeUsers', 'dislikeUsers')->find($request->commentId);

        $likeUsers = $this->comment->likeUsers->pluck('name')->toArray();
        $likeUserList = '';

        if($likeUsers)
        {
            if(in_array(user()->name, $likeUsers)){
                $key = array_search(user()->name, $likeUsers);
                array_splice( $likeUsers, 0, 0, __('modules.tasks.you') );
                unset($likeUsers[$key + 1]);
            }

            $likeUserList = implode(', ', $likeUsers);
            $this->allLikeUsers = $likeUserList;
        }

        $dislikeUsers = $this->comment->dislikeUsers->pluck('name')->toArray();
        $dislikeUserList = '';

        if($dislikeUsers)
        {
            if(in_array(user()->name, $dislikeUsers)){
                $key = array_search (user()->name, $dislikeUsers);
                array_splice( $dislikeUsers, 0, 0, __('modules.tasks.you') );
                unset($dislikeUsers[$key + 1]);
            }

            $dislikeUserList = implode(', ', $dislikeUsers);
            $this->allDislikeUsers = $dislikeUserList;

        }

        $view = view('tasks.comments.comment-emoji', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function emoji($request)
    {
        $newEmoji = new TaskCommentEmoji();
        $newEmoji->user_id = user()->id;
        $newEmoji->comment_id = $request->commentId;
        $newEmoji->emoji_name = $request->emojiName;
        $newEmoji->save();
    }

}
