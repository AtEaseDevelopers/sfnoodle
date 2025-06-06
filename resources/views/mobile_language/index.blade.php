@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('mobile_language_translation.mobile_app_language_translation') }}</h4>
                </div>
                @include('flash::message')

                <div class="card-body">
                    <div class="row">
                        <!-- Left Side - System Language Selection -->
                        <div class="col-md-6">
                            <div class="border p-3 rounded bg-light">
                                <h5 class="mb-3">{{ __('mobile_language_translation.available_languages') }}</h5>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('mobile_language_translation.language') }}</th>
                                            <th>{{ __('mobile_language_translation.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($availableSystemLanguages as $language)
                                        <tr>
                                            <td>{{ $language->name }}</td>
                                            <td>
                                                <form action="{{ route('mobile_language.edit', $language->id) }}" method="GET" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">{{ __('mobile_language_translation.edit') }}</button>
                                                </form>

                                                <form action="{{ route('mobile_language.destroy', $language->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this language?')">{{ __('mobile_language_translation.delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right Side - Import New Language -->
                        <div class="col-md-6">
                            <div class="border p-3 rounded bg-light">
                                <h5 class="mb-3">{{ __('mobile_language_translation.import_new_language') }}</h5>
                                <form method="POST" action="{{ route('mobile_language.import') }}" class="form-inline" id="import-language-form">
                                    @csrf
                                    <div class="form-group w-100">
                                        <label class="mr-2">{{ __('mobile_language_translation.select_language_to_import') }}:</label>
                                        <select name="language" class="form-control language-filter" id="import-language" required>
                                            <option value="">{{ __('mobile_language_translation.choose_language') }}</option>
                                            @foreach($languages as $language)
                                                @unless($availableSystemLanguages->contains('code', $language->code))
                                                    <option value="{{ $language->code }}">
                                                        {{ $language->name }}
                                                    </option>
                                                @endunless
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary ml-2">
                                            Import
                                        </button>
                                    </div>
                                </form>
                                <small class="text-muted">{{ __('mobile_language_translation.only_shows_languages_not_already_imported') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('mobile_language.save') }}" id="translation-form">
        @csrf
        <input type="hidden" name="current_language" id="form-current-language" value="{{ $selectedLanguage }}">

        <!-- Search Field -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="page-search" placeholder="Search pages or text...">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5>{{ __('mobile_language_translation.mobile_app_translation') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="50%">{{ __('mobile_language_translation.default_english_text') }}</th>
                                <th width="50%">{{ __('mobile_language_translation.translation') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($translations as $key => $value)
                                <tr>
                                    <td>{{ $defaultTranslations[$key] ?? '' }}</td>
                                    <td>
                                        <input type="text" 
                                            name="translations[{{ $key }}]" 
                                            value="{{ session('is_imported') ? '' : old('translations.' . $key, $value ?? '') }}"
                                            class="form-control" 
                                            placeholder="Enter translation">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                {{ __('mobile_language_translation.save_all_translations') }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" /> <!-- Font Awesome CSS -->
<style>
    /* Ensure Select2 respects the width */
    .select2-container {
        width: 50% !important; /* Match the w-50 class */
    }
    .select2-container .select2-selection--single {
        height: 38px; /* Match Bootstrap form-control height */
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px; /* Center text vertically */
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px; /* Align arrow */
    }

    /* Hide page sections that don't match the search */
    .page-section {
        display: block;
    }
    .page-section.hidden {
        display: none;
    }

    /* Collapse styling */
    .collapse {
        transition: all 0.3s ease;
    }

    /* Button styling */
    .btn-sm {
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS for collapse -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 on the language dropdown
    $('.language-filter').select2({
        placeholder: 'Choose language',
        allowClear: true,
        width: '100%', // Ensure it fits the form layout
    });

    // Ensure the form submits the selected value correctly
    $('.language-filter').on('select2:select', function(e) {
        console.log('Selected language:', e.params.data.id);
    });

    // Handle import language submission
    $('#import-language-form').on('submit', function(e) {
        var selectedLanguage = $('#import-language').val();
        if (selectedLanguage) {
            $('#form-current-language').val(selectedLanguage); // Update hidden input with imported language
            console.log('Imported language set to:', selectedLanguage);
        }
    });

    // Search filter for translation table - modified to search only by key with LIKE behavior
    $('#page-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase().trim();
        console.log('Search term:', searchTerm); // Debug: Log the search term

        // Get all table rows in the translation table
        $('#translation-form table tbody tr').each(function() {
            var row = $(this);
            // Get the translation key from the input name attribute
            var key = row.find('input').attr('name').toLowerCase().replace('translations[', '').replace(']', '');

            // Debug: Log the key being compared
            console.log('Row key:', key);

            // Check if the search term is a substring of the key (LIKE '%searchTerm%')
            if (searchTerm === '' || key.includes(searchTerm)) {
                row.show();
                console.log('Showing row with key:', key); // Debug: Confirm row is shown
            } else {
                row.hide();
                console.log('Hiding row with key:', key); // Debug: Confirm row is hidden
            }
        });
    });

    // Toggle collapse for each page with accessibility
    $('.show-down').on('click', function() {
        var target = $(this).data('target');
        $(target).collapse('show');
        $(this).hide();
        $(this).siblings('.show-up').show();
        $(this).attr('aria-expanded', 'false');
        $(this).siblings('.show-up').attr('aria-expanded', 'true');
    });

    $('.show-up').on('click', function() {
        var target = $(this).data('target');
        $(target).collapse('hide');
        $(this).hide();
        $(this).siblings('.show-down').show();
        $(this).attr('aria-expanded', 'false');
        $(this).siblings('.show-down').attr('aria-expanded', 'true');
    });

    // Ensure correct button visibility on collapse events
    $('.collapse').on('shown.bs.collapse', function() {
        var target = '#' + $(this).attr('id');
        $('[data-target="' + target + '"].show-down').hide();
        $('[data-target="' + target + '"].show-up').show();
    }).on('hidden.bs.collapse', function() {
        var target = '#' + $(this).attr('id');
        $('[data-target="' + target + '"].show-up').hide();
        $('[data-target="' + target + '"].show-down').show();
    });
});

$(document).ready(function() {
    HideLoad();
});
</script>
@endpush