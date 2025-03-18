@php
    $project = \App\Models\Project::find($notification->data['id']);
@endphp

<x-cards.notification :notification="$notification" :link="route('projects.show', $notification->data['id']).'?tab=rating'"
                      :image="user()->image_url"
                      :title="__('email.projectRating.subject')" :text="$project->project_name"
                      :time="$notification->created_at"/>
