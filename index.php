<?php
// Get variables
$namespace = $_ENV['NAMESPACE'];
$token = $_ENV['TOKEN'];
$apiurl = $_ENV['API_URL'];

// Do curl to API to get status
$curl = curl_init();
$url = $apiurl . "/apis/apps.openshift.io/v1/namespaces/" . $namespace . "/deploymentconfigs";
$header = array();
$header[] = 'Content-Type: application/json';
$header[] = 'Authorization: Bearer ' . $token;

curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_URL, $url);

$response = curl_exec($curl);
curl_close($curl);

$json = json_decode($response);

$states = array(); //Array of states of all DCs.
$states["DCs"] = array();
$states["overall"] = "OK"; // Overall status.

foreach ( $json->items as $item ) {
    $name = $item->metadata->name;
    $replicas = $item->status->replicas;
    $availableReplicas = $item->status->availableReplicas;
    if ( $replicas == 0 ) {
        // This is supposed to not be running.
        $status = "OK";
    }
    elseif ( $availableReplicas == 0 ) {
        // There should be at leat one replica running, but there's not.
        $status = "CRITICAL";
        $states["overall"] = "CRITICAL";
    }
    elseif ( $replicas != $availableReplicas ) {
        // There is at least a replica running, but not all.
        $status = "WARNING";
        if ( $states["overall"] == "OK" ) {
            $states["overall"] = "WARNING";
        }
    }
    elseif ( $replicas == $availableReplicas ) {
        // All replicas are available
        $status = "OK";
    }
    else {
        // We should not be ending up here.
        $status = "UNKNOWN";
    }
    $states["DCs"][$name] = $status;
}

if ( $states["overall"] != "OK" ) {
    // Return code 500 for easier reading within external monitoring.
    http_response_code(500);
}

header('Content-Type: application/json');
echo json_encode($states);
?>
