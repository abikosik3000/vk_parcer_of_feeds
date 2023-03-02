<?php
require __DIR__ . '/vendor/autoload.php';

use React\Promise\Promise;
use React\Promise\Deferred;

class ReactImageDownloader{

    function loadAndSaveImages(array $urls, string $destinationPath): Promise
    {
        $downloadedUrls = [];
        
        //foreach
        $promises = array_map(function ($url) use ($destinationPath, &$downloadedUrls) {

            $deferred = new Deferred();
            $client = new \React\Http\Browser();
            $client->get($url)->then(
                function (\Psr\Http\Message\ResponseInterface $response) use ($url, $destinationPath, $deferred, &$downloadedUrls) {
                    $image = \imagecreatefromstring($response->getBody());
                    if ($image !== false) {
                        $filename = basename($url);
                        $path = $destinationPath . '/' . $filename;
                        \imagepng($image, $path);
                        $downloadedUrls[] = $path;
                        $deferred->resolve([$url, $path]);
                    } else {
                        $deferred->reject(new \RuntimeException("Failed to create image from response for URL: $url"));
                    }
                },
                function ($error) use ($url, $deferred) {
                    $deferred->reject(new \RuntimeException("Failed to fetch image for URL: $url. Error: $error"));
                }
            );



            return $deferred->promise();
        }, $urls);
        

        $deferred_all = new Deferred();

        \React\Promise\all($promises)->then(function ($images) use ($deferred_all, &$downloadedUrls) {
            echo "pach loaded";
            var_dump($downloadedUrls);
            $deferred_all->resolve([$images, $downloadedUrls]);
        });

        return $deferred_all->promise();
    }
}



$loadImages = ["http://abikosru.beget.tech/storage/midle_48971662816156.jpg" 
, "http://abikosru.beget.tech/storage/midle_72161662816156.jpg", "http://abikosru.beget.tech/storage/midle_72161662816156.jpg", "http://abikosru.beget.tech/storage/midle_72161662816156.jpg", "http://abikosru.beget.tech/storage/midle_72161662816156.jpg"];

$loadImages2 =["http://abikosru.beget.tech/storage/midle_72161662816156.jpg"];



$downloader = new ReactImageDownloader();
$loads_image = [];
echo "srart";
$loads_image[] = $downloader->loadAndSaveImages($loadImages, dirname(__FILE__)) ;
$loads_image[] = $downloader->loadAndSaveImages($loadImages2, dirname(__FILE__)) ;


\React\Promise\all($loads_image)->then(function ($images) {

    
    echo "\n end all \n";
});

echo "end osnova";

