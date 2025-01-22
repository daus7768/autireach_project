<?php
// Function to get coordinates from the Google Geocoding API
function getCoordinates($address) {
    $apiKey = 'AIzaSyAH3mt6fAoEg57j2x59It5tVtgtFaIYW6M'; // Replace with your actual API key
    $address = urlencode($address);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data['status'] === 'OK') {
        $latitude = $data['results'][0]['geometry']['location']['lat'];
        $longitude = $data['results'][0]['geometry']['location']['lng'];
        return ['lat' => $latitude, 'lng' => $longitude];
    } else {
        return false; // Handle errors gracefully
    }
}

// Example usage: Retrieve coordinates and embed Street View
$address = "1600 Amphitheatre Parkway, Mountain View, CA"; // Example address
$coordinates = getCoordinates($address);

if ($coordinates) {
    $latitude = $coordinates['lat'];
    $longitude = $coordinates['lng'];

    echo "Latitude: " . $latitude . "<br>";
    echo "Longitude: " . $longitude . "<br>";

    // Embed the Street View using the retrieved coordinates
    $apiKey = 'AIzaSyAH3mt6fAoEg57j2x59It5tVtgtFaIYW6M'; // Replace with your actual API key
    echo "<div class='map-container' style='margin-top: 20px;'>";
    echo "<iframe
            src='https://www.google.com/maps/embed/v1/streetview?key=$apiKey&location=$latitude,$longitude&heading=210&pitch=10&fov=80'
            width='100%'
            height='400'
            frameborder='0'
            allowfullscreen>
          </iframe>";
    echo "</div>";
} else {
    echo "Address not found!";
}
?>
