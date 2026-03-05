<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = app(\App\Http\Controllers\DubController::class);
$request = new \Illuminate\Http\Request();
// Short 10-second video: "Me at the zoo" (the first YouTube video)
$request->replace(['youtube_url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw']);

try {
    echo "Starting dub process...\n";
    $response = $controller->process($request);
    
    if (is_string($response)) {
        echo "Error: " . $response . "\n";
    } else {
        echo "Success: Video ready for download at: " . $response->getFile()->getPathname() . "\n";
    }
} catch (\Exception $e) {
    echo "Exception occurred:\n" . $e->getMessage() . "\n";
}
