<?php
session_start();
error_reporting(E_ERROR | E_PARSE);


// delete files after 1 day
$directory = "uploads/";
foreach (glob($directory . "*") as $file) {
    if (is_file($file)) {
        if (filemtime($file) < strtotime("-1 day")) {
            unlink($file);
        }
    }
}

function getDNS($domain, $space) {
    $valid = array('ionos', '1und1', 'united-internet', 'gmx', 'web.de', 'freenet', 'strato', 'united-domains', 'mail.com', 'fasthosts', 'home.pl', 'arsys', 'sedo.com', 'world4you', 'we22', 'internetx', 'kundenserver.de');
    $domain = trim($domain);
    $dns = dns_get_record("$domain" , DNS_ALL);
    $data = array(
        'host' => $dns[0]['host'],
        'ip' => $dns[0]['ip'],
        'ipv6' => $dns[9]['ipv6'],
        'rname' => $dns[5]['rname'],
        'target' => $dns[6]['target']
    );

    $result = "";
    foreach ($data as $key => $value) {
        if ($value == "") {
            $value = "Not found";
        }
        switch ($key) {
            case 'rname':
                $key = "Provider";
                break;
            case 'target':
                $key = "Mail NS";
                break;
            default:
                $key = ucfirst($key);
                break;
        }
        $key = str_pad($key, 10, '#', STR_PAD_RIGHT);
        foreach (str_split($key) as $char) {
            $placeholder = '&nbsp';
            if ($space != "<br>") {
                $placeholder = ' ';
            } 
            $key = str_replace("#", "$placeholder", $key);
        }
        $result .= "$key: $value $space";
        foreach ($valid as $provider) {
            if (str_contains($value, $provider) !== false) {
                file_put_contents("valid.txt", $domain . "\n", FILE_APPEND);
            }
        }
    }
    return $result . "--------------------------------\n";
}


// get dns infos
if (isset($_POST['domain']) or isset($_GET['file'])) {
    if (isset($_GET['file'])) {
        $userIP = gethostbyname(gethostname());
        if ($userIP == '') {
            $userIP = $_SERVER['REMOTE_ADDR'];
        }
        $filename = $_GET['file'];
        $domains = file_get_contents("uploads/$filename");
        foreach (explode("\n", $domains) as $domain) {
            file_put_contents("uploads/$userIP.txt", getDNS($domain, "\n"), FILE_APPEND);
        }
        unlink("uploads/$filename");
        echo "<script>location.href='?download=true';</script>";
    } else {
        $domain = $_POST['domain'];
        $result = getDNS($domain, "<br>");
    }
        


    $host = $data['host'];
    $ipv4 = $data['ip'];
    $ipv6 = $data['ipv6'];
    $provider = $data['rname'];
    $mailNS = $data['target'];
    
    if (isset($_POST['log'])) {
        $date = date("d.m.Y H:i:s");
        file_put_contents("../backend/dns.txt", "$date\nDomain: $domain\nHost: $host\nIPv4: $ipv4\nIPv6: $ipv6\nProvider: $provider\nMail NS: $mailNS\n\n\n", FILE_APPEND);
    }
}


// file upload
if (isset($_POST['upload'])) {
    $file = $_FILES['file'];
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];
    $fileType = $_FILES['file']['type'];

    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));

    $allowed = array('txt', 'log');

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 10000000) {
                $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                $fileDestination = 'uploads/' . $fileNameNew;
                move_uploaded_file($fileTmpName, $fileDestination);
                header("Location: dns.php?file=$fileNameNew");
            } else {
                echo "Your file is too big!";
            }
        } else {
            echo "There was an error uploading your file!";
        }
    } else {
        echo "You can only upload .txt and .log files!";
    }
}

// download file
if (isset($_GET['download'])) {
    $userIP = gethostbyname(gethostname());
    if ($userIP == '') {
        $userIP = $_SERVER['REMOTE_ADDR'];
    }
    $filename = $_GET['file'];
    $file = "uploads/$userIP.txt";
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    unlink($file);
    exit;
}

echo "
<body style='background-color: #1e1e1e; color: white;'>
    <h1>Get DNS Records</h1>
    <form method='post' action=''>
        <input type='text' name='domain' placeholder='Domain'>
        <input type='checkbox' name='log' value='True'>Log<br><br>
        <input type='submit' value='Send'>
    </form>
    <p style='font-family: monospace;'>$result</p>
    
    <form action='' method='post' enctype='multipart/form-data'>
        <input type='file' name='file'>
        <button type='submit' name='upload'>UPLOAD</button>
    </form>
    <button style='position: absolute; bottom: 60%; width: 60px' onclick='location.href=\"../backend/dns.txt\";'>Log</button>
</body>
    ";
    
    ?>