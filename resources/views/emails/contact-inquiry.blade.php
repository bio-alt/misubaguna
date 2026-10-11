<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #0f172a; font-size: 14px;">
    <h2 style="color: #2b9d9f;">New Website Inquiry</h2>
    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td><strong>Name</strong></td><td>{{ $inquiry['name'] }}</td></tr>
        <tr><td><strong>Company</strong></td><td>{{ $inquiry['company'] }}</td></tr>
        <tr><td><strong>Email</strong></td><td><a href="mailto:{{ $inquiry['email'] }}">{{ $inquiry['email'] }}</a></td></tr>
        <tr><td><strong>Phone / WhatsApp</strong></td><td>{{ $inquiry['phone'] }}</td></tr>
        <tr><td><strong>Interest</strong></td><td>{{ $inquiry['category'] }}</td></tr>
    </table>
    <h3>Message</h3>
    <p style="white-space: pre-line;">{{ $inquiry['message'] }}</p>
    <hr>
    <p style="color: #64748b; font-size: 12px;">Sent from the contact form at {{ url('/contact-us/') }}. Reply to this email to respond directly to the sender.</p>
</body>
</html>
