<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WebScrapingService;

class ScrapeMoreCravingsCommand extends Command
{
    protected $signature = 'scrape:more-cravings {url : The full URL of the More Cravings website page to scrape}';
    protected $description = 'Scrapes the More Cravings website data from the SPA and exports it to a CSV file';

    public function handle(WebScrapingService $scraper)
    {
        $url = $this->argument('url');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Invalid URL provided.');
            return 1;
        }

        $this->info("Starting scraper for: {$url}");

        $result = $scraper->scrapeSpa($url);

        if ($result->status !== 'success') {
            $this->error('Scraping failed: ' . $result->message);
            return 1; 
        }

        $this->info('Data retrieved! Formatting for CSV...');
        
        $data = (array) $result->data;

        // Combine Tags into a comma-separated string
        $tagsString = implode(', ', $data['tags']);

        // Format Opening Hours (One day per line)
        $hoursString = '';
        if (!empty($data['opening_hours'])) {
            $hoursList = [];
            foreach ($data['opening_hours'] as $hourSet) {
                if (is_object($hourSet) && isset($hourSet->dayOfWeek)) {
                    // Split the comma-separated days ("Monday,Tuesday...") into an array
                    $daysArray = is_array($hourSet->dayOfWeek) ? $hourSet->dayOfWeek : explode(',', $hourSet->dayOfWeek);
                    
                    foreach ($daysArray as $day) {
                        $day = trim($day);
                        $hoursList[] = "{$day} {$hourSet->opens} - {$hourSet->closes}";
                    }
                }
            }
            // Join with a newline character so it drops down a line inside the CSV cell
            $hoursString = implode("\n", $hoursList);
        }

        $csvRow = [
            $data['title'] ?? 'N/A',
            $data['image'] ?? 'N/A',
            $data['directions'] ?? 'N/A',
            $data['mobile'] ?? 'N/A',
            $data['email'] ?? 'N/A',
            $data['website'] ?? 'N/A',
            $data['about'] ?? 'N/A',
            $tagsString,
            $hoursString,
            $data['menu'] ?? 'N/A'
        ];

        $headers = [
            'Title', 'Image', 'Directions', 'Mobile', 'Email', 
            'Website', 'About', 'Tags', 'Opening Hours', 'Menu Image'
        ];

        $slug = basename(parse_url($url, PHP_URL_PATH));
        $fileName = "venue_{$slug}_" . date('Y_m_d_His') . '.csv';
        $filePath = storage_path('app/' . $fileName);
        
        $file = fopen($filePath, 'w');
        fputcsv($file, $headers);
        fputcsv($file, $csvRow);
        fclose($file);

        $this->info("Success! Data exported to: {$filePath}");
        
        return 0; 
    }
}