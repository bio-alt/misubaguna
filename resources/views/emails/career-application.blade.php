<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #0f172a; font-size: 14px;">
    <h2 style="color: #2b9d9f;">New Job Application</h2>
    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td><strong>Position</strong></td><td>{{ $application->position }}</td></tr>
        <tr><td><strong>Name</strong></td><td>{{ $application->name }}</td></tr>
        <tr><td><strong>Email</strong></td><td><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></td></tr>
        <tr><td><strong>Phone / WhatsApp</strong></td><td>{{ $application->phone }}</td></tr>
        <tr><td><strong>CV</strong></td><td>{{ $application->cv_original_name }} (attached)</td></tr>
    </table>
    @if ($application->cover_letter)
        <h3>Cover Letter</h3>
        <p style="white-space: pre-line;">{{ $application->cover_letter }}</p>
    @endif
    <hr>
    <p style="color: #64748b; font-size: 12px;">Application #{{ $application->id }} sent from the career form at {{ url('/career/') }}. Reply to this email to respond directly to the applicant.</p>
</body>
</html>
