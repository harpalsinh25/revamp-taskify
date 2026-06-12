@extends('layout')

@section('title')
<?= get_label('terms_privacy_about', 'Terms, Privacy & About') ?>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
        <h4 class="fw-bold mb-0" style="font-size: 1.35rem;"><?= get_label('terms_privacy_about', 'Terms, Privacy & About') ?></h4>
        <div class="d-flex align-items-center gap-3">
            <nav class="breadcrumb mb-0" aria-label="breadcrumb">
                <a class="breadcrumb-item" href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-item"><?= get_label('settings', 'Settings') ?></span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current"><?= get_label('terms_privacy_about', 'Terms, Privacy & About') ?></span>
            </nav>
        </div>
    </div>
    
    <div class="card mb-3 shadow-none border">
        <div class="card-header border-bottom py-2 px-3">
            <div class="list-group list-group-horizontal-md text-md-center border-0 gap-1" style="font-size: 0.85rem;">
                <a class="list-group-item list-group-item-action py-1 px-3 border-0 active rounded" data-bs-toggle="list" href="#privacy-policy"><?= get_label('privacy_policy', 'Privacy Policy') ?></a>
                <a class="list-group-item list-group-item-action py-1 px-3 border-0 rounded" data-bs-toggle="list" href="#terms-conditions"><?= get_label('terms_conditions', 'Terms and Conditions') ?></a>
                <a class="list-group-item list-group-item-action py-1 px-3 border-0 rounded" data-bs-toggle="list" href="#about-us"><?= get_label('about_us', 'About Us') ?></a>
            </div>
        </div>
        <div class="card-body pt-3 px-3 pb-3">
            <div class="tab-content px-0 pt-0">
                <!-- Privacy Policy Tab -->
                <div class="tab-pane fade show active" id="privacy-policy">
                    <form action="{{ route('terms_privacy_about.store') }}" class="form-submit-event" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="dnr">
                        <input type="hidden" name="variable" value="privacy_policy">
                        @csrf
                        @method('PUT')
                        <textarea class="form-control form-control-sm mb-2" name="value" id="privacy_policy" rows="10">@isset($privacy_policy['privacy_policy']){!! $privacy_policy['privacy_policy'] !!}@endisset</textarea>
                        <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
                            <button type="reset" class="btn btn-xs btn-outline-secondary py-1 px-3" style="font-size: 0.8rem;"><?= get_label('cancel', 'Cancel') ?></button>
                            <button type="submit" class="btn btn-xs btn-primary py-1 px-3" style="font-size: 0.8rem;" id="submit_btn"><i class='bx bx-save me-1'></i> <?= get_label('update', 'Update') ?></button>
                        </div>
                    </form>
                </div>

                <!-- Terms and Conditions Tab -->
                <div class="tab-pane fade" id="terms-conditions">
                    <form action="{{ route('terms_privacy_about.store') }}" class="form-submit-event" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="dnr">
                        <input type="hidden" name="variable" value="terms_conditions">
                        @csrf
                        @method('PUT')
                        <textarea class="form-control form-control-sm mb-2" name="value" id="terms_conditions" rows="10">@isset($terms_conditions['terms_conditions']){!! $terms_conditions['terms_conditions'] !!}@endisset</textarea>
                        <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
                            <button type="reset" class="btn btn-xs btn-outline-secondary py-1 px-3" style="font-size: 0.8rem;"><?= get_label('cancel', 'Cancel') ?></button>
                            <button type="submit" class="btn btn-xs btn-primary py-1 px-3" style="font-size: 0.8rem;" id="submit_btn"><i class='bx bx-save me-1'></i> <?= get_label('update', 'Update') ?></button>
                        </div>
                    </form>
                </div>

                <!-- About Us Tab -->
                <div class="tab-pane fade" id="about-us">
                    <form action="{{ route('terms_privacy_about.store') }}" class="form-submit-event" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="dnr">
                        <input type="hidden" name="variable" value="about_us">
                        @csrf
                        @method('PUT')
                        <textarea class="form-control form-control-sm mb-2" name="value" id="about_us" rows="10">@isset($about_us['about_us']){!! $about_us['about_us'] !!}@endisset</textarea>
                        <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
                            <button type="reset" class="btn btn-xs btn-outline-secondary py-1 px-3" style="font-size: 0.8rem;"><?= get_label('cancel', 'Cancel') ?></button>
                            <button type="submit" class="btn btn-xs btn-primary py-1 px-3" style="font-size: 0.8rem;" id="submit_btn"><i class='bx bx-save me-1'></i> <?= get_label('update', 'Update') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection