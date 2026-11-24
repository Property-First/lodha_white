<?php

// =====================================================
// GET DATA FROM FORM
// =====================================================

$project      = isset($_POST['project']) ? trim($_POST['project']) : 'Lodha Whitefield Hope Farm Bangalore';
$name         = isset($_POST['name']) ? trim($_POST['name']) : '';
$email        = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$ip           = getUserIP();


// =====================================================
// 1. SEND LEAD TO ZOHO FLOW / CRM
// =====================================================

$zohoWebhookUrl = "https://flow.zoho.in/60079714926/flow/webhook/incoming?zapikey=1001.3c1bada82efd69764711a638b8c5468f.25c351b350f7121dc202f80fd4c6cc4c&isdebug=false";


// Data matching your Zoho Flow webhook fields
$data = [
    "Project"    => $project,
    "Email"      => $email,
    "LeadSource" => "ads",
    "Phone"      => $phone_number,
    "Name"       => $name
];


// Initialize cURL
$ch = curl_init($zohoWebhookUrl);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Accept: application/json"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$zohoResponse = curl_exec($ch);

$zohoHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$zohoError = curl_error($ch);

curl_close($ch);


// Log Zoho errors if any
if ($zohoError) {
    error_log("Zoho Flow Error: " . $zohoError);
}

if ($zohoHttpCode >= 400) {
    error_log("Zoho Flow HTTP Error: " . $zohoHttpCode);
}


// =====================================================
// 2. SEND EMAIL
// =====================================================
// lmt@property-first.com, vaibhavspf1997@gmail.com

$to = "suneel@property-first.com";

$subject = "New CRM Lead - Lodha Whitefield - " . $project;


// Email message
$txt = "NEW LEAD INQUIRY\r\n";
$txt .= "================\r\n\r\n";

$txt .= "Project: " . $project . "\r\n";
$txt .= "Name: " . $name . "\r\n";
$txt .= "Email: " . $email . "\r\n";
$txt .= "Telephone: " . $phone_number . "\r\n";
$txt .= "IP Address: " . $ip . "\r\n";
$txt .= "Submission Date: " . date('Y-m-d H:i:s') . "\r\n";


// Email headers
$headers = "From: propertyfirstads@gmail.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";


if (!empty($email)) {

    if (mail($to, $subject, $txt, $headers)) {
        error_log("Email sent successfully");
    } else {
        error_log("Email failed to send");
    }

}


// =====================================================
// 3. REDIRECT TO THANK YOU PAGE
// =====================================================

header("Location: thankyou.html");
exit();


// =====================================================
// FUNCTION: GET USER IP
// =====================================================

function getUserIP()
{
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        return $_SERVER["HTTP_CF_CONNECTING_IP"];
    }

    if (
        isset($_SERVER['HTTP_CLIENT_IP']) &&
        filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)
    ) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

        foreach ($forwarded_ips as $forwarded_ip) {

            $forwarded_ip = trim($forwarded_ip);

            if (filter_var($forwarded_ip, FILTER_VALIDATE_IP)) {
                return $forwarded_ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

?>