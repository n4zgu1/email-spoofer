<?php
error_reporting(E_ERROR | E_PARSE);


if (isset($_POST['domain'])) {
    $domain = $_POST['domain'];
    $dns = dns_get_record("$domain" , DNS_ALL);

    $data = array(
        'host' => $dns[0]['host'],
        'ip' => $dns[0]['ip'],
        'ipv6' => $dns[9]['ipv6'],
        'rname' => $dns[5]['rname'],
        'target' => $dns[6]['target']
    );

    
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
        $key = str_replace("#", "&nbsp", $key);
    }
    $result .= "$key: $value<br>";
}

    $host = $data['host'];
    $ipv4 = $data['ip'];
    $ipv6 = $data['ipv6'];
    $provider = $data['rname'];
    $mailNS = $data['target'];
    
    if (isset($_POST['log'])) {
        $date = date("d.m.Y H:i:s");
        file_put_contents("dns.txt", "$date\nDomain: $domain\nHost: $host\nIPv4: $ipv4\nIPv6: $ipv6\nProvider: $provider\nMail NS: $mailNS\n\n", FILE_APPEND);
    }
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
</body>
";

?>