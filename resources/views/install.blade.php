@extends('layout')
@section('title', 'Installer')

@section('content')
    <div class="container-fluid">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner installer-div">
                <div class="card p-4">
                    <!-- Logo -->
                    <div class="mb-4 text-center">
                        <a href="{{ url('install') }}">
                            <img src="{{ asset('storage/logos/default_full_logo.png') }}" width="300px" alt="App Logo" />
                        </a>
                    </div>

                    <!-- Title -->
                    <div class="mb-4 text-center">
                        <h3>APPLICATION INSTALLER</h3>
                        <p>Follow the steps below to install your application</p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <small>Installation Progress</small>
                            <small><span id="progress-text">Step 1 of 3</span></small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" id="progress-bar" style="width: 33.33%" aria-valuenow="33"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Step Navigation -->
                    <ul class="nav nav-pills nav-fill mb-3">
                        <li class="nav-item">
                            <button type="button" class="nav-link active step-nav" id="step1-nav" data-bs-toggle="tab"
                                data-bs-target="#step1" data-step="1">
                                <i class="bx bx-check-shield me-1"></i> System Requirements
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link step-nav" id="step2-nav" data-bs-toggle="tab"
                                data-bs-target="#step2" data-step="2" disabled>
                                <i class="bx bx-cog me-1"></i> Database Configuration
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link step-nav" id="step3-nav" data-bs-toggle="tab"
                                data-bs-target="#step3" data-step="3" disabled>
                                <i class="bx bx-check-square me-1"></i> Complete Installation
                            </button>
                        </li>
                    </ul>

                    <!-- Step Content -->
                    <div class="tab-content">
                        <!-- Step 1 -->
                        <!-- resources/views/installer/step1.blade.php -->
                        <div class="tab-pane fade show active" id="step1">
                            <h4 class="mb-3"><i class="bx bx-check-shield"></i> System Requirements Check</h4>

                            {{-- PHP Version --}}
                            <div class="mb-3 rounded border p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>PHP Version (Required: 8.2+)</span>
                                    @php $phpOk = version_compare(PHP_VERSION, '8.2.0', '>='); @endphp
                                    <span
                                        class="badge bg-label-{{ $phpOk ? 'success' : 'danger' }}">{{ PHP_VERSION }}</span>
                                </div>
                                @unless ($phpOk)
                                    <small class="text-danger">Update your PHP version to 8.2 or higher.</small>
                                @endunless
                            </div>

                            {{-- Required Extensions --}}
                            <div class="mb-3 rounded border p-3">
                                <strong>Required PHP Extensions</strong>
                                @php
                                    $extensions = [
                                        'bcmath',
                                        'ctype',
                                        'fileinfo',
                                        'json',
                                        'mbstring',
                                        'openssl',
                                        'pdo',
                                        'tokenizer',
                                        'xml',
                                    ];
                                @endphp
                                @foreach ($extensions as $ext)
                                    @php $extOk = extension_loaded($ext); @endphp
                                    <div class="d-flex justify-content-between">
                                        <span>{{ strtoupper($ext) }}</span>
                                        <i
                                            class="bx bx-{{ $extOk ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                                    </div>
                                    @unless ($extOk)
                                        <small class="text-danger ms-2">Enable the {{ $ext }} extension in
                                            php.ini.</small>
                                    @endunless
                                @endforeach
                            </div>

                            {{-- Writable Directories --}}
                            <div class="mb-3 rounded border p-3">
                                <strong>Directory Permissions</strong>
                                @php
                                    $dirs = ['storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache'];
                                @endphp
                                @foreach ($dirs as $dir)
                                    @php $dirOk = is_writable(base_path($dir)); @endphp
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $dir }}</span>
                                        <i
                                            class="bx bx-{{ $dirOk ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                                    </div>
                                    @unless ($dirOk)
                                        <small class="text-danger ms-2">Run: <code>chmod -R 775
                                                {{ $dir }}</code></small>
                                    @endunless
                                @endforeach
                            </div>

                            {{-- Symlink Support --}}
                            <div class="mb-3 rounded border p-3">
                                <strong>Symlink Support</strong>
                                @php $symlinkOk = function_exists('symlink') && is_callable('symlink'); @endphp
                                <div class="d-flex justify-content-between">
                                    <span>symlink() function available</span>
                                    <i
                                        class="bx bx-{{ $symlinkOk ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                                </div>
                                @unless ($symlinkOk)
                                    <small class="text-danger">Enable symlink() in PHP or contact your hosting provider.</small>
                                @endunless
                            </div>




                            <div class="text-end">
                                <button class="btn btn-primary" id="proceed-step2">Proceed to Database
                                    Configuration</button>
                            </div>
                        </div>


                        <!-- Step 2 -->
                        <div class="tab-pane fade" id="step2">
                            <h4 class="mb-3"><i class="bx bx-data"></i> Database Configuration</h4>
                            <form action="{{ url('installer/config-db') }}" method="POST" class="form-submit-event">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Database Name *</label>
                                        <input class="form-control" type="text" name="db_name"
                                            value="{{ old('db_name') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Host Name *</label>
                                        <input class="form-control" type="text" name="db_host_name"
                                            value="{{ old('db_host_name', 'localhost') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Username *</label>
                                        <input class="form-control" type="text" name="db_user_name"
                                            value="{{ old('db_user_name') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Password</label>
                                        <input class="form-control" type="password" name="db_password">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary"
                                        id="back-step1">Back</button>
                                    <button type="submit" class="btn btn-primary">Test Connection & Continue</button>
                                </div>
                            </form>
                        </div>

                        <!-- Step 3 -->
                        <div class="tab-pane fade" id="step3">
                            <h4 class="mb-3"><i class="bx bx-user-plus"></i> Admin Account Setup</h4>
                            <form action="{{ url('installer/install') }}" method="POST" class="form-submit-event">
                                @csrf
                                <input type="hidden" name="redirect_url" value="{{ url('/') }}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>First Name *</label>
                                        <input class="form-control" type="text" name="first_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Last Name *</label>
                                        <input class="form-control" type="text" name="last_name" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label>Email Address *</label>
                                        <input class="form-control" type="email" name="email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Password *</label>
                                        <input class="form-control" type="password" name="password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Confirm Password *</label>
                                        <input class="form-control" type="password" name="password_confirmation"
                                            required>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary"
                                        id="back-step2">Back</button>
                                    <button type="submit" class="btn btn-primary">Complete Installation</button>
                                </div>
                            </form>
                        </div>
                    </div> <!-- /tab-content -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');

            function updateProgress(step) {
                const percent = (step / 3) * 100;
                progressBar.style.width = percent + '%';
                progressText.textContent = `Step ${step} of 3`;
            }

            document.getElementById('proceed-step2').addEventListener('click', () => {
                document.getElementById('step2-nav').disabled = false;
                document.getElementById('step2-nav').click();
                updateProgress(2);
            });
            document.getElementById('back-step1').addEventListener('click', () => {
                document.getElementById('step1-nav').click();
                updateProgress(1);
            });
            document.getElementById('back-step2').addEventListener('click', () => {
                document.getElementById('step2-nav').click();
                updateProgress(2);
            });
        });
    </script>
@endsection
