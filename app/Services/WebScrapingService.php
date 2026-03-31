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

            // --- THE PAGINATION FIX ---
            $keepClicking = true;
            $clicks = 0;
            $maxClicks = 100; // Safety limit: Prevents an infinite loop if the site bugs out

            while ($keepClicking && $clicks < $maxClicks) {
                // 1. Scroll to the bottom to ensure the button is visible
                $client->executeScript("window.scrollTo(0, document.body.scrollHeight);");
                sleep(1); 

                // 2. Ask Chrome to find and click the "Show More" button
                $clicked = $client->executeScript("
                    // Look for any button or link containing 'Show More' or 'Load More'
                    let elements = Array.from(document.querySelectorAll('button, a'));
                    let showMoreBtn = elements.find(el => {
                        let text = el.innerText.toLowerCase().trim();
                        return text === 'show more' || text === 'load more';
                    });

                    // If the button exists, is visible, and isn't disabled, click it!
                    if (showMoreBtn && showMoreBtn.offsetParent !== null && !showMoreBtn.disabled) {
                        showMoreBtn.click();
                        return true;
                    }
                    return false;
                ");

                // 3. If we clicked it, wait for the new venues to load. If not, we are done!
                if ($clicked) {
                    $clicks++;
                    // Give the Next.js API 2 seconds to fetch the new batch of venues
                    sleep(2); 
                } else {
                    $keepClicking = false;
                }
            }

            // --- END PAGINATION FIX ---

            // Now that ALL venues are loaded into the DOM, extract their URLs
            $payload = $client->executeScript("
                let links = Array.from(document.querySelectorAll('a[href^=\"/en/venues/\"]'));
                let urls = links.map(a => a.href).filter(href => href.length > 'https://www.morecravings.com/en/venues/'.length);
                // Use Set to automatically remove any duplicate URLs
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
        $extension = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $driverPath = base_path("drivers/chromedriver{$extension}");
        if (PHP_OS_FAMILY === 'Windows') {
            $driverPath = str_replace('/', '\\', $driverPath);
        }

        $client = \Symfony\Component\Panther\Client::createChromeClient($driverPath, [
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);

        try {
            // ==========================================
            // STEP 1: LOAD MAIN PAGE (YOUR EXACT CODE)
            // ==========================================
            $client->request('GET', $url);
            $client->waitFor('#main-content', 10);

            // Scroll the main page to ensure the "Menu" button physically renders
            $client->executeScript("window.scrollTo(0, document.body.scrollHeight / 2);");
            sleep(1);
            $client->executeScript("window.scrollTo(0, document.body.scrollHeight);");
            sleep(2);

            $payload = $client->executeScript("
                let result = { 
                    schema: null, 
                    html: document.documentElement.innerHTML,
                    email: document.querySelector('a[href^=\"mailto:\"]') ? document.querySelector('a[href^=\"mailto:\"]').href.replace('mailto:', '') : null,
                    mobile: document.querySelector('a[href^=\"tel:\"]') ? document.querySelector('a[href^=\"tel:\"]').href.replace('tel:', '') : null,
                    menuLink: null
                };

                // Find Schema
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

                // Find Menu Link (Much more aggressive targeting)
                let menuAnchor = document.querySelector('a[href*=\"/menu\"]');
                if (!menuAnchor) {
                    let allLinks = Array.from(document.querySelectorAll('a'));
                    menuAnchor = allLinks.find(a => a.href && a.href.toLowerCase().includes('/menu/'));
                }
                
                if (menuAnchor) {
                    result.menuLink = menuAnchor.href; 
                }

                return JSON.stringify(result);
            ");

            $extractedData = json_decode($payload);

            if (!$extractedData || !$extractedData->schema) {
                throw new \Exception('Could not find the Restaurant Schema data.');
            }

            $restaurantData = $extractedData->schema;
            $rawHtml = $extractedData->html;

            // Formats
            $tags = [];
            if (isset($restaurantData->servesCuisine)) {
                $tags = is_array($restaurantData->servesCuisine) ? $restaurantData->servesCuisine : [$restaurantData->servesCuisine];
            }
            if (preg_match('/outlets_dressCode_name.*?en[\\\\":]+([^\\\\"]+)/i', $rawHtml, $matches)) {
                $tags[] = $matches[1];
            }

            $directions = null;
            if (isset($restaurantData->geo->latitude) && isset($restaurantData->geo->longitude)) {
                $directions = "https://www.google.com/maps/search/?api=1&query={$restaurantData->geo->latitude},{$restaurantData->geo->longitude}";
            }

            // ==========================================
            // STEP 2: LOAD MENU PAGE (YOUR EXACT CODE)
            // ==========================================
            $menuListFormatted = 'N/A (Menu link not found on main page)';
            
            if (!empty($extractedData->menuLink)) {
                try {
                    $client->request('GET', $extractedData->menuLink);
                    sleep(2); 
                    
                    // Scroll menu page to fetch food items
                    $client->executeScript("window.scrollTo(0, document.body.scrollHeight);");
                    sleep(3); 

                    $menuListFormatted = $client->executeScript("
                        document.querySelectorAll('header, footer, nav, [class*=\"header\" i], [class*=\"footer\" i], [class*=\"nav\" i], [class*=\"banner\" i]').forEach(el => el.remove());
                        
                        let main = document.querySelector('main') || document.body;
                        let items = [];
                        
                        let menuNodes = document.querySelectorAll('[class*=\"menuItem\" i], [class*=\"MenuItem\" i], .menu-item, [class*=\"accordionContent\" i] > div');
                        
                        if (menuNodes.length > 0) {
                            menuNodes.forEach(node => {
                                let htmlContent = node.innerHTML;
                                let spacedHtml = htmlContent.replace(/<\\/(div|p|h[1-6]|li|strong|span)>/gi, ' |SEP| ');
                                let tempDiv = document.createElement('div');
                                tempDiv.innerHTML = spacedHtml;
                                
                                let parts = tempDiv.textContent.split('|SEP|').map(s => s.trim()).filter(s => s.length > 0 && s !== '-');
                                let text = parts.join(' - ');

                                if (text.length > 5 && /[0-9]/.test(text) && !text.toLowerCase().includes('terms of use')) {
                                    if (!items.includes(text)) items.push(text);
                                }
                            });
                        }

                        if (items.length === 0) {
                            let allElements = main.querySelectorAll('*');
                            allElements.forEach(el => {
                                if (el.children.length === 0 && (el.innerText.includes('GEL') || el.innerText.includes('AED') || el.innerText.includes('USD'))) {
                                    let container = el.parentElement.parentElement;
                                    if (container && container.innerText.length < 300) {
                                        let htmlContent = container.innerHTML;
                                        let spacedHtml = htmlContent.replace(/<\\/(div|p|h[1-6]|li|strong|span)>/gi, ' |SEP| ');
                                        let tempDiv = document.createElement('div');
                                        tempDiv.innerHTML = spacedHtml;
                                        let text = tempDiv.textContent.split('|SEP|').map(s => s.trim()).filter(s => s.length > 0).join(' - ');
                                        
                                        if (text.length > 5 && !items.includes(text)) items.push(text);
                                    }
                                }
                            });
                        }

                        let pdfLink = document.querySelector('a[href$=\".pdf\" i]');
                        let images = Array.from(main.querySelectorAll('img')).filter(img => 
                            (img.alt && img.alt.toLowerCase().includes('menu')) || img.width > 200
                        );

                        if (items.length > 0) {
                            return items.join(' | '); 
                        } else if (pdfLink) {
                            return 'PDF Menu: ' + pdfLink.href;
                        } else if (images.length > 0) {
                            let imageUrls = images.map(img => img.src).filter(src => !src.includes('data:image'));
                            let cleanUrls = imageUrls.map(url => {
                                try { return new URLSearchParams(url.split('?')[1]).get('url') || url; } catch(e) { return url; }
                            });
                            return 'Menu Image(s): ' + [...new Set(cleanUrls)].join(' | ');
                        } else {
                            return 'N/A (Menu page loaded, but no food items or images were found)';
                        }
                    ");

                } catch (\Exception $menuException) {
                    $menuListFormatted = 'Error loading menu: ' . $menuException->getMessage();
                }
            }

            // ==========================================
            // STEP 3: SURGICAL GALLERY IMAGE EXTRACTION
            // ==========================================
            $allImages = [];

            // 1. Keep the main Hero Image as a baseline fallback
            if (isset($restaurantData->image)) {
                if (is_array($restaurantData->image)) {
                    $allImages = array_merge($allImages, $restaurantData->image);
                } else if (is_string($restaurantData->image)) {
                    $allImages[] = $restaurantData->image;
                }
            }

            // 2. Automatically generate the Gallery URL
            $galleryLink = rtrim(explode('?', $url)[0], '/') . '/gallery';

            try {
                $client->request('GET', $galleryLink);
                sleep(2); // Wait for the Schema to load

                // Find the EXACT ImageGallery Schema you discovered
                $galleryPayload = $client->executeScript("
                    let galleryUrls = [];
                    let scripts = document.querySelectorAll('script[type=\"application/ld+json\"]');
                    
                    for (let script of scripts) {
                        try {
                            let text = script.textContent || script.innerText || script.innerHTML;
                            let data = JSON.parse(text);
                            // This ensures we ONLY grab images strictly tied to this venue's gallery
                            if (data['@type'] === 'ImageGallery' && data.image) {
                                galleryUrls = Array.isArray(data.image) ? data.image : [data.image];
                                break;
                            }
                        } catch (e) {}
                    }
                    return JSON.stringify(galleryUrls);
                ");

                $galleryImages = json_decode($galleryPayload, true);
                if (is_array($galleryImages) && count($galleryImages) > 0) {
                    $allImages = array_merge($allImages, $galleryImages);
                }

            } catch (\Exception $galleryException) {
                // Silently skip if gallery fails, it will just output the main hero image
            }

            // Clean the URLs to remove exact duplicates and query parameters (like ?wid=1920)
            $cleanImages = array_map(function($imgUrl) {
                return explode('?', $imgUrl)[0];
            }, $allImages);
            $cleanImages = array_unique(array_filter($cleanImages));

            $imagesString = !empty($cleanImages) ? implode(' | ', $cleanImages) : 'N/A';

            return (object)[
                'status' => 'success',
                'data' => [
                    'title'         => $restaurantData->name ?? null,
                    'images'         => $imagesString, // Now populated with the clean ImageGallery array!
                    'directions'    => $directions,
                    'mobile'        => $extractedData->mobile ?? $restaurantData->telephone ?? null,
                    'email'         => $extractedData->email ?? $restaurantData->email ?? null,
                    'website'       => $restaurantData->url ?? $url,
                    'about'         => $restaurantData->description ?? null,
                    'tags'          => $tags,
                    'opening_hours' => $restaurantData->openingHoursSpecification ?? [],
                    'menu'          => $menuListFormatted
                ]
            ];

        } catch (\Exception $e) {
            return (object)['status' => 'error', 'message' => $e->getMessage()];
        } finally {
            $client->quit();
        }
    }
}