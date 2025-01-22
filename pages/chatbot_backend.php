<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['user_message'];

    // LangFlow API details
    $apiUrl = "http://localhost:7860/api/v1/predict";
    $flowId = "0a35335b-2c84-4f3c-828a-a3be53636bf2";

    // Prepare the payload
    $payload = json_encode([
        'flow_id' => $flowId,
        'input' => $userMessage
    ]);

    // Initialize cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer AIzaSyBL-QAQCpyvxsd_G_ascvHofQoa9R04ik0'
        
    ]);

    // Send the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        echo json_encode(['reply' => $responseData['output']]);
    } else {
        echo json_encode(['reply' => 'Sorry, something went wrong.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
