<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Tracker Alert</title>
</head>
<body>
    <p>Hello,</p>

    <p>The following tracker exception was found for {{ $user->name }} on {{ $reportDate->format('d-M-Y') }}.</p>

    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <th align="left">Employee</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th align="left">Email</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th align="left">Tracked Hours</th>
            <td>{{ number_format($user->tracked_hours, 2) }}</td>
        </tr>
        <tr>
            <th align="left">Issue</th>
            <td>{{ $user->alert_reason === 'absent' ? '0 hours tracked' : '13+ hours tracked' }}</td>
        </tr>
    </table>

    <p style="margin-top: 16px;"><strong>{{ $user->question_text }}</strong></p>

    <p>Please reply with the reason.</p>
</body>
</html>
