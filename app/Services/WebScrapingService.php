<?php

namespace App\Services;

use Symfony\Component\Panther\Client;

class WebScrapingService
{
    /**
     * Scrapes the main listing page to get all individual venue URLs
     */
    public function getAllVenueUrls(string $listingUrl = 'https://www.morecravings.com/en/venues')
    {
        $extension = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $driverPath = base_path("drivers/chromedriver{$extension}");
        if (PHP_OS_FAMILY === 'Windows') {
            $driverPath = str_replace('/', '\\', $driverPath);
        }

        $client = Client::createChromeClient($driverPath, [
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);

        try {
            $client->request('GET', $listingUrl);
            $client->waitFor('#main-content', 10);

            // Scroll down a few times to trigger any lazy-loaded Next.js elements
            for ($i = 0; $i < 3; $i++) {
                $client->executeScript("window.scrollTo(0, document.body.scrollHeight);");
                sleep(1); 
            }

            // Extract all unique links that point to a specific venue
            $payload = $client->executeScript("
                let links = Array.from(document.querySelectorAll('a[href^=\"/en/venues/\"]'));
                let urls = links.map(a => a.href).filter(href => href.length > 'https://www.morecravings.com/en/venues/'.length);
                return JSON.stringify([...new Set(urls)]);
            ");

            return json_decode($payload);

        } catch (\Exception $e) {
            return [];
        } finally {
            $client->quit();
        }
    }

    /**
     * Your existing method that scrapes a single venue (Keep this exactly as we perfected it earlier)
     */
    public function scrapeSpa(string $url)
    {
        // 1. Automatically determine the correct extension based on the Operating System
        $extension = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $driverPath = base_path("drivers/chromedriver{$extension}");

        // 2. Format the directory separators specifically for Windows if necessary
        if (PHP_OS_FAMILY === 'Windows') {
            $driverPath = str_replace('/', '\\', $driverPath);
        }

        // 3. Initialize the client
        $client = Client::createChromeClient($driverPath, [
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);

        try {
            $client->request('GET', $url);

            // Wait for the main content
            $client->waitFor('#main-content', 5);

            // Extract the data using Chrome's JS engine
            $payload = $client->executeScript("
                let result = { 
                    schema: null, 
                    html: document.documentElement.innerHTML,
                    email: document.querySelector('a[href^=\"mailto:\"]') ? document.querySelector('a[href^=\"mailto:\"]').href.replace('mailto:', '') : null,
                    mobile: document.querySelector('a[href^=\"tel:\"]') ? document.querySelector('a[href^=\"tel:\"]').href.replace('tel:', '') : null
                };

                let scripts = document.querySelectorAll('script[type=\"application/ld+json\"]');
                for (let script of scripts) {
                    try {
                        let text = script.textContent || script.innerText || script.innerHTML;
                        let data = JSON.parse(text);
                        if (data['@type'] === 'Restaurant') {
                            result.schema = data;
                            break;
                        }
                    } catch (e) {}
                }
                return JSON.stringify(result);
            ");

            $extractedData = json_decode($payload);

            if (!$extractedData || !$extractedData->schema) {
                throw new \Exception('Could not find the Restaurant Schema data.');
            }

            $restaurantData = $extractedData->schema;
            $rawHtml = $extractedData->html;

            // 1. Tags: Combine Cuisines + Dress Code
            $tags = [];
            if (isset($restaurantData->servesCuisine)) {
                $tags = is_array($restaurantData->servesCuisine) ? $restaurantData->servesCuisine : [$restaurantData->servesCuisine];
            }

            // Regex to find "Casual" even when wrapped in messy Next.js escaped quotes like \"en\":\"Casual\"
            if (preg_match('/outlets_dressCode_name.*?en[\\\\":]+([^\\\\"]+)/i', $rawHtml, $matches)) {
                $tags[] = $matches[1];
            }

            // 2. Menu Image: Bypass lazy-loading via Regex
            $menuImage = null;
            if (preg_match('/(https(?:%3A%2F%2F|:\/\/)[^"\'\\\\\s<>]+(?:Wide-Hor|menu)[^"\'\\\\\s<>&]*)/i', $rawHtml, $matches)) {
                $menuImage = urldecode($matches[1]);
            }

            // 3. Directions: Generate it dynamically using the Schema Coordinates
            $directions = null;
            if (isset($restaurantData->geo->latitude) && isset($restaurantData->geo->longitude)) {
                $lat = $restaurantData->geo->latitude;
                $lng = $restaurantData->geo->longitude;
                // Creates a standard Google Maps link
                $directions = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
            }

            return (object)[
                'status' => 'success',
                'data' => [
                    'title'         => $restaurantData->name ?? null,
                    'image'         => $restaurantData->image[0] ?? null,
                    'directions'    => $directions,
                    'mobile'        => $extractedData->mobile ?? $restaurantData->telephone ?? null,
                    'email'         => $extractedData->email ?? $restaurantData->email ?? null,
                    'website'       => $restaurantData->url ?? $url,
                    'about'         => $restaurantData->description ?? null,
                    'tags'          => $tags,
                    'opening_hours' => $restaurantData->openingHoursSpecification ?? [],
                    'menu'          => $menuImage
                ]
            ];
        } catch (\Exception $e) {
            return (object)[
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        } finally {
            $client->quit();
        }
    }
}
