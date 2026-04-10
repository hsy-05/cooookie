<!DOCTYPE html>
<html>
<head>
    <title>新留言通知</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>您好，網站收到了一筆新的聯絡我們表單！</h2>
    <p>以下是使用者的留言內容：</p>

    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
        <p><strong>姓名：</strong> {{ $contact->name }}</p>
        <p><strong>信箱：</strong> {{ $contact->email }}</p>
        <p><strong>主旨：</strong> {{ $contact->subject }}</p>
        <p><strong>內容：</strong><br> {{ nl2br(e($contact->message)) }}</p>
    </div>

    <p style="margin-top: 20px; font-size: 0.9em; color: #888;">
        發送時間：{{ $contact->created_at }}<br>
        發送 IP：{{ $contact->ip_address }}
    </p>
</body>
</html>
