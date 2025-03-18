<x-cards.notification :notification="$notification" link="javascript:;"
                      :image="company()->logo_url"
                      :title="__('email.newUser.subject') . ' ' . $companyName . ' !'"
                      :time="$notification->created_at"/>
