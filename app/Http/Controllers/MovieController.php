<?php

namespace App\Http\Controllers;

use DOMXPath;
use DOMDocument;
use App\Models\Movie;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\MovieResource;


class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Base URL for fetching movie data
        $baseUrl = 'https://www.fullhdfilmizlesene.de/filmrobot/';
        $extension = '?tarz=&tur=&yil=&imdb=6x&hd=';

        // Get the first and last page numbers
        $pageNumbers = $this->getFirstAndLastPageNumbers($baseUrl, $extension);

        // If page numbers cannot be retrieved, return an error
        if ($pageNumbers === null) {
            return response()->json('No movies found. Please try again.');
        }

        // Select a random page
        $selectedPage = $this->findRandomPage(1, 1);

        // Get random movie information from the selected page
        $randomMovieInfo = $this->getRandomMovieInfo($baseUrl, $selectedPage, $extension);

        //TO-DO
            // isMovieExist tam çalışmıyor buna bakılacak
      
        // If the movie already exists in the database, return an error
        if ($this->isMovieExist($randomMovieInfo['title'], $randomMovieInfo['url'], $selectedPage, $randomMovieInfo['randomIndex'])) {
            return response()->json('This movie already exists in the database.');
        }

        // Compile movie information
        $movieInfo = $this->compilationOfMovieInformation($randomMovieInfo['url']);


        // Create and save the movie
        $this->createAndSaveMovie($movieInfo, $selectedPage, $randomMovieInfo['url'], $randomMovieInfo['randomIndex']);

        // Return success message
        return response()->json('Movie successfully saved.' . '/' . $randomMovieInfo['randomIndex']);
    }

    private function getFirstAndLastPageNumbers($baseUrl, $extension)
    {
        $paginationXpath = $this->requestWebsite($baseUrl . $extension);
        $pagination = $paginationXpath->query('//div[@class="sayfalama"]/a');
        $firstPageNumber = $pagination->item(1)->textContent;
        $lastPageNumber = $pagination->item($pagination->length - 2)->textContent;
        return [
            'first_page' => $firstPageNumber,
            'last_page' => $lastPageNumber
        ];
    }

    private function findRandomPage($firstPageNumber, $lastPageNumber)
    {
        return rand($firstPageNumber, $lastPageNumber);
    }

    private function getRandomMovieInfo($baseUrl, $selectedPage, $extension)
    {
        $selectedPageUrl = $baseUrl . $selectedPage . $extension;
        $xpath = $this->requestWebsite($selectedPageUrl);
        $movieElements = $xpath->query('//ul[@class="list"]/li[@class="film"]');

        // If movies are found, select a random movie
        if ($movieElements->length > 0) {
            $randomIndex = rand(0, $movieElements->length - 1);
            $randomMovieElement = $movieElements->item($randomIndex);

            // Get the title and URL of the selected movie
            $titleNode = $xpath->query('.//a[@class="tt"]', $randomMovieElement);
            $title = $titleNode->item(0)->nodeValue;

            $urlNode = $xpath->query('.//a[@class="tt"]/@href', $randomMovieElement);
            $url = $urlNode->item(0)->nodeValue;

            return [
                'title' => $title,
                'url' => $url,
                'randomIndex' => $randomIndex,
            ];
        } else {
            echo "No movies found.";
        }
    }

    private function requestWebsite($url)
    {
        $response = Http::get($url);
        $htmlContent = $response->body();
        $dom = new DOMDocument();
        $dom->loadHTML($htmlContent, LIBXML_NOERROR);
        return new DOMXPath($dom);
    }

    private function isMovieExist($name, $url, $number_of_page, $number_of_movie)
    {
        // Tüm koşulları içeren bir sorgu yapalım
        $existingMovie = Movie::where('name', $name)
            ->where('url', $url)
            ->where('number_of_page', $number_of_page)
            ->where('number_of_movie', $number_of_movie)
            ->first(); // İlk eşleşen filmi al, eğer varsa

        // Eğer $existingMovie null değilse (yani bir film bulunduysa), bu film var olarak kabul edilir
        // Aksi halde (yani film bulunamadıysa), bu film yok olarak kabul edilir
        return $existingMovie !== null;
    }

    private function compilationOfMovieInformation($movieUrl)
    {
        $xpath = $this->requestWebsite($movieUrl);

        $movieTitle = $xpath->query("//div[@class='header-sol']/div[@class='izle-titles']/h1/a")->item(0)->nodeValue;
        $imdbRating = $xpath->query("//div[@class='imdb-ic']/span")->item(0)->nodeValue;
        $summary = $xpath->query("//div[@class='ozet-ic']/p")->item(0)->nodeValue;
        $dataSrcset = $xpath->query("//source/@data-srcset")->item(0)->nodeValue;
        $ulNode = $xpath->query("//div[@class='film-info']/ul")->item(0);
        $liNodes = $xpath->query(".//li", $ulNode);
        $listItems = [];

        foreach ($liNodes as $liNode) {
            $listItems[] = $liNode->nodeValue;
        }

        return [
            'movieTitle' => $movieTitle,
            'imdbRating' => $imdbRating,
            'summary' => $summary,
            'imgUrl' => $dataSrcset,
            'listItems' => $listItems,
        ];
    }


    private function createAndSaveMovie($movieInfo, $selectedPage, $url, $randomIndex)
    {
        // Create a new Movie instance and populate it with the retrieved data
        $movie = new Movie();
        $movie->fill([
            'name' => $movieInfo['movieTitle'],
            'score' => $movieInfo['imdbRating'],
            'description' => $movieInfo['summary'],
            'picture' => $movieInfo['imgUrl'],
            'content' => json_encode($movieInfo['listItems']), // Assuming listItems is JSON
            'number_of_page' => $selectedPage,
            'url' => $url,
            'number_of_movie' => $randomIndex
            // You can set other attributes as needed
        ]);

        // Save the movie to the database
        $movie->save();
    }




    public function getMovie ()
    {
        $movies = Movie::all(); // Retrieve all movies
        return $movies->toArray();  
    }

}