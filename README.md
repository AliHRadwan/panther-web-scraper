# Panther Web Scraper: More Cravings

A Laravel-based web scraping application utilizing Symfony Panther to extract data specifically from modern, Single-Page Applications (SPAs) like [More Cravings](https://www.morecravings.com/). 

This tool features a dynamic custom Artisan command that accepts any venue page URL as an argument, scraping the target and exporting the data directly into a timestamped CSV file.

## Features
* **Dynamic Target URLs:** Pass any specific More Cravings venue URL directly into the CLI command.
* **SPA Handling:** Uses headless Chrome browser drivers to effectively scrape JavaScript-rendered, dynamic content.
* **Automated CSV Export:** Neatly formats and exports all scraped data into a timestamped CSV file for easy analysis.

## Prerequisites
* PHP & Composer
* Laravel environment
* Google Chrome (Required for Panther browser drivers)

## Installation & Setup

**1. Clone the repository and navigate to the directory**
```bash
git clone git@github.com:AliHRadwan/panther-web-scraper.git
cd panther-web-scraper
```

**2. Install dependencies**
```bash
composer install
```

**3. Install browser drivers**
Note: Chrome browser must be installed on your system.
```bash
vendor/bin/bdi detect drivers
```

## Usage

**1. Command Syntax:**
```bash
php artisan scrape:more-cravings "<url>"
```

**2. Examples:**
```bash
php artisan scrape:more-cravings "https://www.morecravings.com/en/venues/brasserie-freedom-restaurant-and-bar"

php artisan scrape:more-cravings "https://www.morecravings.com/en/venues/billiard-bar"

php artisan scrape:more-cravings "https://www.morecravings.com/en/venues/baroro"
```

**3. Output**
The scraped data is automatically saved to the storage/app/ directory as a CSV file. The filename is dynamically generated using the venue name and a timestamp to prevent overwriting.
```bash
panther-web-scraper/storage/app/{VENUE_NAME}_{TIMESTAMP}.csv
```

```bash
Title,Image,Directions,Mobile,Email,Website,About,Tags,"Opening Hours","Menu Image"
Bar'Oro,https://cache.marriott.com/content/dam/marriott-renditions/CAIRZ/cairz-bar-0026-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0457960,31.2321630",+20225778899,rc.cairz.restaurants.reservations@ritzcarlton.com,https://www.morecravings.com/en/venues/baroro,"Overlooking the Nile, Bar’Oro brings the elegance of a classic cocktail bar to downtown Cairo. Signature cocktails are paired with Italian-inspired aperitifs, or small bites, and a walk-humidor houses an impressive selection of cigars.","International, Smart Casual","Monday 12:00pm - 01:00am
Tuesday 12:00pm - 01:00am
Wednesday 12:00pm - 01:00am
Thursday 12:00pm - 01:00am
Friday 12:00pm - 01:00am
Saturday 12:00pm - 01:00am
Sunday 12:00pm - 01:00am",https://www.morecravings.com/en/menus/3239
```
---
## Author
Ali H. Radwan
GitHub: [@AliHRadwan](https://github.com/alihradwan)