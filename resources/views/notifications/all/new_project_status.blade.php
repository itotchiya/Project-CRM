<x-cards.notification :notification="$notification" :link="route('projects.show', $notification->data['id'])"
                      :image="user()->image_url"
                      :title="__('email.newProjectStatus.subject')" :text="$notification->data['project_name']"
                      :time="$notification->created_at"/>
