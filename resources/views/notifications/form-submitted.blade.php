<x-mail::message>
# You've got a new form submission.

{!! $entry->display_html !!}

<x-mail::button :url="route('filament.admin.resources.forms.edit', $entry->form->id)">
    View form
</x-mail::button>

</x-mail::message>
