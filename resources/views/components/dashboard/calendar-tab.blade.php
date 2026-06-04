@props(['alert' => '', 'alertUrl' => '', 'alertAction' => '', 'calendarId' => '', 'listComponent' => '', 'data' => []])

<ul class="nav nav-tabs justify-content-start mb-3 border-0" role="tablist">
    <li class="nav-item" role="presentation">
        <button
            type="button"
            class="nav-link active list-button border-0 border-bottom border-2 border-transparent"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#{{ $calendarId }}-list"
            aria-controls="{{ $calendarId }}-list"
            aria-selected="true"
            style="border-radius: 0;">
            {{ get_label('list', 'List') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button
            type="button"
            class="nav-link calendar-button border-0 border-bottom border-2 border-transparent"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#{{ $calendarId }}-calendar"
            aria-controls="{{ $calendarId }}-calendar"
            aria-selected="false"
            style="border-radius: 0;">
            {{ get_label('calendar', 'Calendar') }}
        </button>
    </li>
</ul>

<style>
    .nav-tabs .nav-link.active.list-button,
    .nav-tabs .nav-link.active.calendar-button {
        border-bottom-color: var(--bs-primary) !important;
        color: var(--bs-primary) !important;
    }
    /* override to green like the image */
    #navs-top-upcoming-birthdays .nav-link.active.list-button,
    #navs-top-upcoming-birthdays .nav-link.active.calendar-button {
        border-bottom-color: var(--bs-success) !important;
        color: var(--bs-success) !important;
    }
    #navs-top-upcoming-work-anniversaries .nav-link.active.list-button,
    #navs-top-upcoming-work-anniversaries .nav-link.active.calendar-button {
        border-bottom-color: var(--bs-warning) !important;
        color: var(--bs-warning) !important;
    }
</style>

<div class="tab-content no-shadow p-0">
    <div class="tab-pane fade active show" id="{{ $calendarId }}-list" role="tabpanel">
        @if ($alert)
            <div class="alert alert-primary alert-dismissible" role="alert">
                {{ $alert }},
                <a href="{{ $alertUrl }}">{{ $alertAction }}</a>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <x-dynamic-component :component="$listComponent" :users="$data" />
    </div>
    <div class="tab-pane fade" id="{{ $calendarId }}-calendar" role="tabpanel">
        <div id="{{ $calendarId }}"></div>
    </div>
</div>
