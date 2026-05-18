@php
   $workflowContext = $workflowContext ?? 'sekretariat';
@endphp

@if($workflowContext === 'sekretariat')
   @include('admin.Kepegawaian.persuratan.surat_masuk._workflow-sekretariat')
@elseif($workflowContext === 'kadis')
   @include('admin.Kepegawaian.persuratan.surat_masuk._workflow-kadis')
@elseif($workflowContext === 'unit')
   @include('admin.Kepegawaian.persuratan.surat_masuk._workflow-unit')
@endif
