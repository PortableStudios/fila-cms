You've got a new form submission.

{!! $entry->display_html !!}

<a href="{{ route('filament.admin.resources.forms.edit', $entry->form->id) }}">View Form</a>
