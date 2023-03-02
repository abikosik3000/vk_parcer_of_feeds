<?php
require __DIR__ . '/vendor/autoload.php';

use React\Promise\Promise;
use React\Promise\Deferred;

class ReactImageDownloader{ // implement AcyncImageDownloader

    private $destinationPath;

    public function __construct($destinationPath){
        $this->destinationPath = $destinationPath;
    }

    function load_images(array $urls): Promise
    {
        $loaded_image = [];

        foreach($urls as $url){
            $deferred = new Deferred();
            $client = new \React\Http\Browser();
            $client->get($url)->then(
                function (\Psr\Http\Message\ResponseInterface $response) use ($url, $deferred, &$downloadedUrls) {
                    $image = imagecreatefromstring($response->getBody());
                    if ($image !== false) {
                        $filename = uniqid() . "." . pathinfo($url, PATHINFO_EXTENSION);
                        $path = $this->destinationPath . '/' . $filename;
                        imagepng($image, $path);
                        $deferred->resolve($path);
                    } else {
                        $deferred->resolve(false);
                        //$deferred->reject(new \RuntimeException("Failed to create for $url"));
                    }
                },
                function ($error) use ($url, $deferred) {
                    $deferred->resolve(false);
                    //$deferred->reject(new \RuntimeException("Failed load img for $url error: $error"));
                }
            );
            $loaded_image[] = $deferred->promise();
        }
        

        $deferred_all = new Deferred();

        \React\Promise\all($loaded_image)->then(function ($loaded_image) use ($deferred_all) {
            $deferred_all->resolve($loaded_image);
        });

        return $deferred_all->promise();
    }
}

/*
$loadImages = ["http://abikosru.beget.tech/storage/midle_48971662816156.jpg" 
, "http://abikosru.beget.tech/storage/midle_72161662816156.jpg", 
"http://abikosru.beget.tech/storage/midle_72161662816156.jp", 
"http://abikosru.beget.tech/storage/midle_72161662816156.jpg", 
"http://abikosru.beget.tech/storage/midle_72161662816156.jpg"];

$loadImages2 =["http://abikosru.beget.tech/storage/midle_72161662816156.jpg"];

echo "srart";
$downloader = new ReactImageDownloader(dirname(__FILE__)."/upload");
$loads_image = [];
$loads_image[] = $downloader->load_images($loadImages) ;
$loads_image[] = $downloader->load_images($loadImages2) ;

\React\Promise\all($loads_image)->then (function ($images) {
    var_dump($images); 
    echo "\n end all \n"; 
}); 
echo "end";
*/




