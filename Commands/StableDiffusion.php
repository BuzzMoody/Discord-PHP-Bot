<?php

// It's good practice to have these at the top during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

use React\Http\Browser;
use Psr\Http\Message\ResponseInterface;
// use Discord\Parts\Channel\Attachment; // Uncomment if used
// use Discord\Builders\MessageBuilder; // Uncomment if used
use React\Filesystem\Filesystem;
use React\EventLoop\Loop; // Required for Filesystem::get()
use React\Filesystem\Factory;
use React\Filesystem\Node\DirectoryInterface;
use React\Filesystem\Node\NodeInterface;

function StableDiffusion($message, $args) {
    echo "[DEBUG] StableDiffusion function started." . PHP_EOL;

    global $keys;

    if (!isset($keys['cloud']) || empty($keys['cloud'])) {
        echo "[ERROR] 'cloud' key is missing or empty in global \$keys." . PHP_EOL;
        return; // Or return a rejected promise if the function should return one
    }
    $apicode = $keys['cloud'];
    $prompt = $args;

    if (empty($prompt)) {
        echo "[ERROR] Prompt is empty." . PHP_EOL;
        return;
    }
    echo "[DEBUG] Prompt: " . $prompt . PHP_EOL;

    echo "[DEBUG] Attempting to get gcloud token..." . PHP_EOL;
    $gcloudCmdOutput = shell_exec('gcloud auth print-access-token 2>&1');

    if ($gcloudCmdOutput === null) {
        echo "[ERROR] shell_exec('gcloud auth print-access-token') failed. Make sure gcloud CLI is installed, configured, and in PATH." . PHP_EOL;
        return;
    }

    $gcloud = trim($gcloudCmdOutput);

    if (empty($gcloud) || stripos($gcloud, 'error') !== false || strlen($gcloud) < 20) { // Basic sanity check for token
        echo "[ERROR] Failed to get a valid gcloud token. Output: " . $gcloudCmdOutput . PHP_EOL;
        return;
    }
    echo "[DEBUG] gcloud token obtained (first 10 chars): " . substr($gcloud, 0, 10) . "..." . PHP_EOL;

    $model = "imagen-3.0-generate-002"; // Consider making this configurable
    $url = "https://australia-southeast1-aiplatform.googleapis.com/v1/projects/{$apicode}/locations/australia-southeast1/publishers/google/models/{$model}:predict";
    echo "[DEBUG] Request URL: " . $url . PHP_EOL;

    $postData = [
        "instances" => [["prompt" => $prompt]],
        "parameters" => [
            "aspectRatio" => "16:9", "sampleCount" => 1, "negativePrompt" => "",
            "enhancePrompt" => false, // "personGeneration" => "", "safetySetting" => "", // Removed potentially problematic empty strings, API might expect them to be absent or have specific values. Consult API docs.
            "addWatermark" => true, "includeRaiReason" => true, "language" => "auto",
        ]
    ];
    $postDataEnc = json_encode($postData);

    if ($postDataEnc === false) {
        echo "[ERROR] json_encode failed. Error: " . json_last_error_msg() . PHP_EOL;
        return;
    }
    echo "[DEBUG] Post data encoded." . PHP_EOL;

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $gcloud,
        // 'Content-Length' is usually handled by React\Http\Browser
    ];

    // If not in a context where Loop is already available, you might need to pass it.
    // For ReactPHP components, Loop::get() is often sufficient.
    $browser = new Browser(null, Loop::get());
    echo "[DEBUG] Browser initialized. Sending POST request..." . PHP_EOL;

    $browser->post($url, $headers, $postDataEnc)->then(
        function (ResponseInterface $response) use ($message) { // Removed $filePath from `use`
            echo "[DEBUG] HTTP POST request responded. Status: " . $response->getStatusCode() . PHP_EOL;
            $responseBody = (string) $response->getBody(); // Cast to string to get full body

            if (empty($responseBody)) {
                echo "[ERROR] HTTP Response body is empty." . PHP_EOL;
                return;
            }
            echo "[DEBUG] Response body (first 200 chars): " . substr($responseBody, 0, 200) . "..." . PHP_EOL;

            $responseData = json_decode($responseBody, true); // Decode as associative array

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "[ERROR] Failed to decode JSON response. Error: " . json_last_error_msg() . PHP_EOL;
                echo "[DEBUG] Raw response for JSON error: " . $responseBody . PHP_EOL;
                return;
            }

            // Defensive checks for API response structure
            if (!isset($responseData['predictions'][0]['bytesBase64Encoded']) || !isset($responseData['predictions'][0]['mimeType'])) {
                echo "[ERROR] Unexpected API response structure. 'bytesBase64Encoded' or 'mimeType' not found." . PHP_EOL;
                echo "[DEBUG] Full Decoded Response Data: " . print_r($responseData, true) . PHP_EOL;
                // Check if there's an error message from the API
                if (isset($responseData['error'])) {
                    echo "[API ERROR] " . print_r($responseData['error'], true) . PHP_EOL;
                }
                return;
            }
            echo "[DEBUG] Required fields found in API response." . PHP_EOL;

            $base64 = $responseData['predictions'][0]['bytesBase64Encoded'];
            $mimeType = $responseData['predictions'][0]['mimeType'];

            $bin = base64_decode($base64, true); // Use strict mode
            if ($bin === false) {
                echo "[ERROR] base64_decode failed. The base64 string might be invalid." . PHP_EOL;
                return;
            }
            echo "[DEBUG] Image data decoded from base64." . PHP_EOL;

            $ext = preg_replace('/[^a-z0-9]/i', '', str_replace('image/', '', $mimeType)) ?: 'png';
            $filename = 'image_' . time() . '_' . uniqid() . '.' . $ext;

            // Construct a more robust path. __DIR__ refers to the directory of the current PHP file.
            // Adjust this path according to your actual directory structure.
            $dirPath = "/home/buzz/Bots/Media/AI/";
            $filePath = $dirPath . $filename;
            echo "[DEBUG] Target file path: " . $filePath . PHP_EOL;

            // Ensure the directory exists and is writable
            if (!is_dir($dirPath)) {
                echo "[DEBUG] Directory not found. Attempting to create: " . $dirPath . PHP_EOL;
                if (!mkdir($dirPath, 0775, true)) { // Create recursively
                    echo "[ERROR] Failed to create directory: " . $dirPath . PHP_EOL;
                    return;
                }
                echo "[DEBUG] Directory created: " . $dirPath . PHP_EOL;
            } elseif (!is_writable($dirPath)) {
                echo "[ERROR] Directory is not writable: " . $dirPath . PHP_EOL;
                return;
            }

            // Get the filesystem instance using the event loop
            $filesystem = \React\Filesystem\Factory::create();
            $file = $filesystem->file($filePath);
            echo "[DEBUG] Filesystem object created. Attempting to save file..." . PHP_EOL;

            $file->putContents($bin)->then(
                function () use ($filePath, $message, $filename) { // Added $filename to use
                    echo "SUCCESS: Image successfully saved to: " . $filePath . PHP_EOL;

                    // Example of how you might use $message (if it's a DiscordPHP message object)
                    // Ensure $message and its properties are valid before calling methods on them.
                    // if (isset($message->channel) && method_exists($message->channel, 'sendFile')) {
                    //     echo "[DEBUG] Attempting to send file to Discord channel..." . PHP_EOL;
                    //     $message->channel->sendFile($filePath, $filename)->then(
                    //         function() {
                    //             echo "[DEBUG] File sent to Discord successfully." . PHP_EOL;
                    //         },
                    //         function(Exception $e) {
                    //             echo "[ERROR] Failed to send file to Discord: " . $e->getMessage() . PHP_EOL;
                    //         }
                    //     );
                    // }
                },
                function (Exception $e) use ($filePath) {
                    echo "[FILESYSTEM ERROR] Error saving image to " . $filePath . ": " . $e->getMessage() . PHP_EOL;
                    echo "[FILESYSTEM ERROR TRACE] " . $e->getTraceAsString() . PHP_EOL;
                }
            );
        },
        function (Exception $e) {
            echo "[HTTP REQUEST ERROR] Failed to make HTTP request: " . $e->getMessage() . PHP_EOL;
            if ($e->getPrevious()) {
                echo "[HTTP REQUEST PREVIOUS ERROR] " . $e->getPrevious()->getMessage() . PHP_EOL;
            }
            echo "[HTTP REQUEST ERROR TRACE] " . $e->getTraceAsString() . PHP_EOL;
        }
    );

    echo "[DEBUG] StableDiffusion function synchronous execution finished. Asynchronous operations pending." . PHP_EOL;
    // If this script is standalone and not part of a larger ReactPHP application,
    // you would need to run the event loop here:
    // Loop::get()->run();
    // However, if it's part of DiscordPHP or a similar framework, that framework manages the loop.
}

// --- Example of how to call it for standalone testing ---
/*
// Ensure Composer's autoloader is included
require __DIR__ . '/vendor/autoload.php';

// Mock global $keys
global $keys;
$keys = ['cloud' => 'your-gcp-project-id']; // <--- REPLACE with your actual project ID

// Mock $message object if needed for testing outside DiscordPHP
$message = new stdClass(); // Simple mock
// $message->channel = new class { // More complex mock if sendFile is tested
//     public function sendFile($filePath, $filename) {
//         echo "[MOCK DISCORD] Sending file: {$filename} from {$filePath}" . PHP_EOL;
//         return \React\Promise\resolve(); // Mock sendFile returning a promise
//     }
// };

$testPrompt = "a futuristic cityscape at sunset";
StableDiffusion($message, $testPrompt);

// CRITICAL FOR STANDALONE: Run the event loop to allow promises to resolve
// If this code is part of a larger ReactPHP application (e.g., DiscordPHP bot),
// that application will handle running the loop, and this line should NOT be here.
if (php_sapi_name() === 'cli' && !defined('EVENT_LOOP_RUNNING')) { // Basic check
    echo "[DEBUG] Starting ReactPHP Event Loop for standalone execution..." . PHP_EOL;
    define('EVENT_LOOP_RUNNING', true); // Prevent multiple runs if script is included
    Loop::get()->run();
    echo "[DEBUG] ReactPHP Event Loop finished." . PHP_EOL;
}
*/