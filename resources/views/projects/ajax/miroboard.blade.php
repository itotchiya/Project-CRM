@php
$viewFilePermission = user()->permission('view_miroboard');
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active mt-5" role="tabpanel" aria-labelledby="nav-email-tab">
    <iframe width="100%" height="800" src="https://miro.com/app/live-embed/{{$project->miro_board_id}}" frameBorder="0" scrolling="no" allowFullScreen></iframe>
</div>
<!-- TAB CONTENT END -->
