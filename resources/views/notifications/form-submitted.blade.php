<fila-cms::layouts.email>
    <p>You've got a new form submission.</p>

    {!! $entry->display_html !!}

    <a href="{{ route('filament.admin.resources.forms.edit', $entry->form->id) }}">View Form</a>
</fila-cms::layouts.email>
