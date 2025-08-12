<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter Preview</title>
</head>
<body style="font-family: Segoe UI, sans-serif; background: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.05);">

        {{-- Header with Logo --}}
        <div style="background: #f1f1f1; text-align: center; padding: 20px;">
            <img src="{{ asset($logo_url) }}" alt="Company Logo" style="max-height: 80px;">
        </div>

        {{-- Content --}}
        <div style="padding: 30px; font-size: 15px; color: #333; line-height: 1.6;">
            {!! $content !!}
        </div>

        {{-- Footer --}}
        <div style="background: #f1f1f1; text-align: center; padding: 15px; font-size: 12px; color: #777;">
            &copy; {{ date('Y') }} {{ $company_title ?? env('APP_NAME') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
