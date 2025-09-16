<?php

namespace Plugins\Letter\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Plugins\Letter\Models\LetterVariable;
use Plugins\Letter\Notifications\LetterEmailNotification;

class LetterHelper
{
    /**
     * Process letter content by replacing variables with their values.
     */
    public static function processContent($content, $user = null)
    {
        $variables = self::getAllVariables($user);

        if (isset($variables['company_logo_url'])) {
            $variables['COMPANY_LOGO'] = '<img src="' . $variables['company_logo_url'] . '" alt="Company Logo" style="max-width: 200px; height: auto;">';
        } else {
            $variables['COMPANY_LOGO'] = '';
        }

        foreach ($variables as $key => $value) {
            $content = str_replace('{' . strtoupper($key) . '}', $value, $content);
        }

        return $content;
    }

    /**
     * Return available variables grouped for UI display.
     */
    public static function getAvailableVariables()
    {
        return [
            'SYSTEM' => self::getSystemVariables(),
            'CUSTOM' => self::getCustomVariables(),
        ];
    }

    /**
     * Get all variable key => value pairs for processing.
     */
    public static function getAllVariables($user = null)
    {
        $variables = [];

        $variables = array_merge($variables, self::getSystemVariableValues($user));

        $customVars = LetterVariable::where('workspace_id', Auth::user()->workspace_id)
            ->where('is_active', true)
            ->pluck('value', 'name')
            ->mapWithKeys(fn ($value, $key) => [strtoupper($key) => $value])
            ->toArray();

        return array_merge($variables, $customVars);
    }

    /**
     * System variable label definitions.
     */
    public static function getSystemVariables()
    {
        return [
            'USER_NAME' => 'User Full Name',
            'FIRST_NAME' => 'User First Name',
            'LAST_NAME' => 'User Last Name',
            'USER_EMAIL' => 'User Email',
            'USER_PHONE' => 'User Phone',
            'USER_DESIGNATION' => 'User Designation',
            'USER_DEPARTMENT' => 'User Department',
            'USER_JOIN_DATE' => 'User Join Date',
            'USER_ID' => 'User ID',
            'CURRENT_DATE' => 'Current Date',
            'CURRENT_MONTH' => 'Current Month',
            'CURRENT_YEAR' => 'Current Year',
            'COMPANY_TITLE' => 'Company Title',
            'COMPANY_LOGO' => 'Company Logo',
        ];
    }

    /**
     * System variable key => value mappings for replacement.
     */
    public static function getSystemVariableValues($user = null)
    {
        $now = Carbon::now();

        return [
            'USER_NAME' => $user->name ?? '[User Name]',
            'FIRST_NAME' => $user->first_name ?? '[First Name]',
            'LAST_NAME' => $user->last_name ?? '[Last Name]',
            'USER_EMAIL' => $user->email ?? '[User Email]',
            'USER_PHONE' => $user->phone ?? '[Phone Number]',
            'USER_DESIGNATION' => $user->designation ?? '[Designation]',
            'USER_DEPARTMENT' => $user->department ?? '[Department]',
            'USER_JOIN_DATE' => $user && $user->joining_date ? Carbon::parse($user->joining_date)->format('d M Y') : '[Join Date]',
            'USER_ID' => $user->user_id ?? '[User ID]',
            'CURRENT_DATE' => $now->format('d M Y'),
            'CURRENT_MONTH' => $now->format('F Y'),
            'CURRENT_YEAR' => $now->format('Y'),
            'COMPANY_TITLE' => get_settings('general_settings')['company_title'] ?? 'Taskify',
            'COMPANY_LOGO' => 'storage/'.get_settings('general_settings')['full_logo'] ?? 'storage/logos/default_full_logo.png',
        ];
    }

    /**
     * Return custom workspace variables as UPPERCASE => Label.
     */
    public static function getCustomVariables()
    {
        return LetterVariable::where('workspace_id', Auth::user()->workspace_id)
            ->where('is_active', true)
            ->pluck('label', 'name')
            ->mapWithKeys(fn ($label, $key) => [strtoupper($key) => $label])
            ->toArray();
    }

    /**
     * Predefined categories for letters.
     */
    public static function getLetterCategories()
    {
        return [
            'offer' => 'Offer Letter',
            'experience' => 'Experience Letter',
            'warning' => 'Warning Letter',
            'appreciation' => 'Appreciation Letter',
            'increment' => 'Increment Letter',
            'promotion' => 'Promotion Letter',
            'termination' => 'Termination Letter',
            'resignation' => 'Resignation Acceptance',
            'confirmation' => 'Confirmation Letter',
            'transfer' => 'Transfer Letter',
            'other' => 'Other',
        ];
    }

    /**
     * Send a letter email with PDF attached.
     */
    public static function sendLetterEmail($email, $subject, $message, $pdf, $letter)
    {
        $recipient = (object) ['email' => $email];

        $recipient->notify(new LetterEmailNotification($recipient, [
            'subject' => $subject,
            'message' => $message,
            'letter' => $letter,
            'pdf' => $pdf,
        ]));
    }

    /**
     * Sample letter content for previews and defaults.
     */
    public static function getSampleContent($category)
    {
        $samples = [
            'offer' => '
            <h2 style="text-align: center;">Offer of Employment</h2>
            <p>{CURRENT_DATE}</p>
            <p>Dear {USER_NAME},</p>
            <p>We are delighted to offer you the position of {USER_DESIGNATION} at {COMPANY_TITLE}, effective {CURRENT_DATE}. Your role in the {USER_DEPARTMENT} will be integral to our continued success.</p>
            <p>Your responsibilities will include contributing to departmental objectives and collaborating with our team to achieve organizational goals. We are confident your skills and expertise will make a significant impact.</p>
            <p>Please contact our HR Department at {USER_PHONE} or {USER_EMAIL} to confirm your acceptance by {CURRENT_DATE + 7 days}. Detailed terms of employment will be provided upon acceptance.</p>
            <p>We look forward to welcoming you to {COMPANY_TITLE}.</p>
            <p>Best regards,<br>HR Department<br>{COMPANY_TITLE}</p>
        ',
            'experience' => '
            <h2 style="text-align: center;">Certificate of Employment</h2>
            <p>{CURRENT_DATE}</p>
            <p>To Whom It May Concern,</p>
            <p>This certifies that {USER_NAME} was employed with {COMPANY_TITLE} as a {USER_DESIGNATION} in the {USER_DEPARTMENT} from {USER_JOIN_DATE} to {CURRENT_DATE}.</p>
            <p>During their tenure, {FIRST_NAME} fulfilled their responsibilities with dedication and professionalism, contributing to the success of their team.</p>
            <p>We wish {FIRST_NAME} the best in their future endeavors.</p>
            <p>Sincerely,<br>HR Department<br>{COMPANY_TITLE}</p>
        ',
            'warning' => '
            <h2 style="text-align: center;">Formal Warning Notice</h2>
            <p>{CURRENT_DATE}</p>
            <p>Dear {USER_NAME},</p>
            <p>This letter is a formal notice regarding concerns about your performance/conduct as {USER_DESIGNATION} in the {USER_DEPARTMENT}, observed on {CURRENT_DATE}.</p>
            <p>We expect immediate improvement to align with {COMPANY_TITLE}’s standards. Please contact HR at {USER_EMAIL} or {USER_PHONE} to discuss support options. Failure to improve by {CURRENT_MONTH}’s end may lead to further action.</p>
            <p>Acknowledge receipt by contacting HR within 3 days.</p>
            <p>Regards,<br>HR Department<br>{COMPANY_TITLE}</p>
        ',
        ];

        return $samples[$category] ?? '
        <h2 style="text-align: center;">Formal Correspondence</h2>
        <p>{CURRENT_DATE}</p>
        <p>Dear {USER_NAME},</p>
        <p>This is a formal communication from {COMPANY_TITLE} regarding your role as {USER_DESIGNATION} in the {USER_DEPARTMENT}.</p>
        <p>For further details, contact HR at {USER_EMAIL} or {USER_PHONE}.</p>
        <p>Regards,<br>HR Department<br>{COMPANY_TITLE}</p>
    ';
    }
}
