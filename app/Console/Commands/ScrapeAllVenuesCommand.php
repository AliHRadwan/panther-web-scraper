<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WebScrapingService;

class ScrapeAllVenuesCommand extends Command
{
    // The command to run in the terminal
    protected $signature = 'scrape:all-venues';

    protected $description = 'Scrapes all venue URLs and compiles their data into a master CSV';

    public function handle(WebScrapingService $scraper)
    {
        $this->info("Step 1: Fetching all venue URLs from the main page...");
        
        // 1. Get all the URLs
        $urls = $scraper->getAllVenueUrls();
        $urlCount = count($urls);

        if ($urlCount === 0) {
            $this->error("Failed to find any venue URLs. The site layout may have changed.");
            return 1;
        }

        $this->info("Found {$urlCount} venues!");

        // 2. Save just the URLs to a separate CSV (As requested)
        $urlFileName = 'venue_urls_list_' . date('Y_m_d_His') . '.csv';
        $urlFilePath = storage_path('app/' . $urlFileName);
        $urlFile = fopen($urlFilePath, 'w');
        fputcsv($urlFile, ['Venue URL']);
        foreach ($urls as $url) {
            fputcsv($urlFile, [$url]);
        }
        fclose($urlFile);
        $this->info("Saved list of URLs to: {$urlFilePath}");

        // 3. Prepare the Master CSV file
        $masterFileName = 'MASTER_VENUE_DATA_' . date('Y_m_d_His') . '.csv';
        $masterFilePath = storage_path('app/' . $masterFileName);
        $masterFile = fopen($masterFilePath, 'w');
        
        $headers = [
            'Title', 'Images', 'Directions', 'Mobile', 'Email', 
            'Website', 'About', 'Tags', 'Opening Hours', 'Menu Items'
        ];
        fputcsv($masterFile, $headers);

        // 4. Loop through every URL, scrape it, and append to Master CSV
        $this->info("Step 2: Scraping individual venues. This will take a while...");
        
        // Create a visual progress bar in the terminal
        $bar = $this->output->createProgressBar($urlCount);
        $bar->start();

        foreach ($urls as $url) {
            // Call the service for each individual URL
            $result = $scraper->scrapeSpa($url);

            if ($result->status === 'success') {
                $data = (array) $result->data;

                // Format Tags
                $tagsString = implode(', ', $data['tags']);

                // Format Hours
                $hoursString = '';
                if (!empty($data['opening_hours'])) {
                    $hoursList = [];
                    foreach ($data['opening_hours'] as $hourSet) {
                        if (is_object($hourSet) && isset($hourSet->dayOfWeek)) {
                            $daysArray = is_array($hourSet->dayOfWeek) ? $hourSet->dayOfWeek : explode(',', $hourSet->dayOfWeek);
                            foreach ($daysArray as $day) {
                                $day = trim($day);
                                $hoursList[] = "{$day} {$hourSet->opens} - {$hourSet->closes}";
                            }
                        }
                    }
                    $hoursString = implode("\n", $hoursList);
                }

                // Compile Row Data
                $csvRow = [
                    $data['title'] ?? 'N/A',
                    $data['images'] ?? 'N/A',
                    $data['directions'] ?? 'N/A',
                    $data['mobile'] ?? 'N/A',
                    $data['email'] ?? 'N/A',
                    $data['website'] ?? 'N/A',
                    $data['about'] ?? 'N/A',
                    $tagsString,
                    $hoursString,
                    $data['menu'] ?? 'N/A'
                ];

                // Write immediately to the CSV so data isn't lost if the script crashes halfway through
                fputcsv($masterFile, $csvRow);
            } else {
                // Log the error but keep the loop running for the next venue
                $this->error("\nFailed to scrape {$url}: " . $result->message);
            }

            $bar->advance();
        }

        $bar->finish();
        fclose($masterFile);

        $this->info("\n\nSuccess! All venue data exported to: {$masterFilePath}");
        return 0;
    }
}