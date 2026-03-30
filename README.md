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

## A. If you want to scrape a specific venue URL
**1. Command Syntax:**
```bash
php artisan scrape:more-cravings "<url>"
```

**2. Example:**
```bash
php artisan scrape:more-cravings "https://www.morecravings.com/en/venues/brasserie-freedom-restaurant-and-bar"
```

**3. Output**
The scraped data is automatically saved to the storage/app/ directory as a CSV file.
The filename is dynamically generated using the venue name and a timestamp to prevent overwriting.
```bash
panther-web-scraper/storage/app/[VENUE_NAME]_[TIMESTAMP].csv
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

## B. If you want to scrape all the venues from the More Cravings
**1. Command Syntax:**
```bash
php artisan scrape:all-venues
```

**2. Output**
The scraped data is automatically saved to the storage/app/ directory as a CSV files. 
The venues' URLs list is stored to a CSV file with the name venue_urls_list_[timestamp].csv
The scraped data of all venues are stored to a CSV file with the name MASTER_VENUE_DATA_[timestamp].csv
```bash
panther-web-scraper/storage/app/venue_urls_list_[timestamp].csv.csv
panther-web-scraper/storage/app/MASTER_VENUE_DATA_[timestamp].csv
```

```bash
"Venue URL"
https://www.morecravings.com/en/venues/plateau
https://www.morecravings.com/en/venues/the-view-lounge-bar
https://www.morecravings.com/en/venues/lobby-lounge-4
https://www.morecravings.com/en/venues/baroro
https://www.morecravings.com/en/venues/the-bar-1
https://www.morecravings.com/en/venues/bab-el-sharq
https://www.morecravings.com/en/venues/vivo
https://www.morecravings.com/en/venues/culina
https://www.morecravings.com/en/venues/roys-smokehouse
https://www.morecravings.com/en/venues/ristorante-tuscany-1
https://www.morecravings.com/en/venues/egyptian-nights
https://www.morecravings.com/en/venues/omar-s-cafe
https://www.morecravings.com/en/venues/garden-promenade-caf
https://www.morecravings.com/en/venues/the-bakery
https://www.morecravings.com/en/venues/saraya-gallery-1
https://www.morecravings.com/en/venues/billiard-bar

```

```bash
Title,Image,Directions,Mobile,Email,Website,About,Tags,"Opening Hours","Menu Image"
Plateau,https://cache.marriott.com/content/dam/marriott-renditions/CAIJW/caijw-plateau-0210-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.071909603397437,31.43442210287171",+20224115588,fb.jwcairo@marriott.com,https://www.morecravings.com/en/venues/plateau,"Step onto our charming outdoor terrace at Plateau, the newest dining attraction at our Cairo resort. Lounge on a spacious open-air patio and choose from an extensive restaurant menu of salads, soups and burgers, as well as our signature drinks.","International, Casual","Monday 07:00am - 01:00am
Tuesday 07:00am - 01:00am
Wednesday 07:00am - 01:00am
Thursday 07:00am - 01:00am
Friday 07:00am - 01:00am
Saturday 07:00am - 01:00am
Sunday 07:00am - 01:00am",N/A
"The View Lounge and Bar",https://cache.marriott.com/is/image/marriotts7prod/jw-caijw-the-view-lounge-and-bar-12443:Square,"https://www.google.com/maps/search/?api=1&query=30.071909603397437,31.43442210287171",+20224115588,fb.jwcairo@marriott.com,https://www.morecravings.com/en/venues/the-view-lounge-bar,"Stop by The View Lounge & Bar to enjoy restaurant dining, craft cocktails and light bites under the vaulted ceilings of our Cairo resort. Open 24 hours, The View also offers a sophisticated high-tea experience in a welcoming, elegant atmosphere.","International, Casual","Monday 12:00am - 12:00am
Tuesday 12:00am - 12:00am
Wednesday 12:00am - 12:00am
Thursday 12:00am - 12:00am
Friday 12:00am - 12:00am
Saturday 12:00am - 12:00am
Sunday 12:00am - 12:00am",N/A
"Lobby Lounge",https://cache.marriott.com/content/dam/marriott-renditions/CAIRZ/cairz-lobby-0021-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0457960,31.2321630",+20225778899,rc.cairz.restaurants.reservations@ritzcarlton.com,https://www.morecravings.com/en/venues/lobby-lounge-4,"Marked with its historic and contemporary masterpieces, this Cairo restaurant provides an elegant setting well suited for professional breakfast meetings or an evening of cocktails and relaxing music.","International, Smart Casual","Monday 12:00am - 12:00am
Tuesday 12:00am - 12:00am
Wednesday 12:00am - 12:00am
Thursday 12:00am - 12:00am
Friday 12:00am - 12:00am
Saturday 12:00am - 12:00am
Sunday 12:00am - 12:00am",https://www.morecravings.com/en/menus/790
Bar'Oro,https://cache.marriott.com/content/dam/marriott-renditions/CAIRZ/cairz-bar-0026-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0457960,31.2321630",+20225778899,rc.cairz.restaurants.reservations@ritzcarlton.com,https://www.morecravings.com/en/venues/baroro,"Overlooking the Nile, Bar’Oro brings the elegance of a classic cocktail bar to downtown Cairo. Signature cocktails are paired with Italian-inspired aperitifs, or small bites, and a walk-humidor houses an impressive selection of cigars.","International, Smart Casual","Monday 12:00pm - 01:00am
Tuesday 12:00pm - 01:00am
Wednesday 12:00pm - 01:00am
Thursday 12:00pm - 01:00am
Friday 12:00pm - 01:00am
Saturday 12:00pm - 01:00am
Sunday 12:00pm - 01:00am",https://www.morecravings.com/en/menus/3239
"The Bar",https://cache.marriott.com/content/dam/marriott-renditions/CAIRZ/cairz-bar-0025-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0457960,31.2321630",+20225778899,rc.cairz.restaurants.reservations@ritzcarlton.com,https://www.morecravings.com/en/venues/the-bar-1,"Mixologists craft classic cocktails, along with their own creations, and the menu features fine wines, champagnes and light snacks.","International, Smart Casual","Monday 12:00pm - 03:00am
Tuesday 12:00pm - 03:00am
Wednesday 12:00pm - 03:00am
Thursday 12:00pm - 03:00am
Friday 12:00pm - 03:00am
Saturday 12:00pm - 03:00am
Sunday 12:00pm - 03:00am",https://www.morecravings.com/en/menus/2366
"Bab El-Sharq",https://cache.marriott.com/content/dam/marriott-renditions/CAIRZ/cairz-restaurant-0029-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0457960,31.2321630",+20225778899,rc.cairz.restaurants.reservations@ritzcarlton.com,https://www.morecravings.com/en/venues/bab-el-sharq,"Bab El Sharq recreates the traditional Middle-Eastern cuisine in an oriental setting overlooking the Egyptian Museum. Pair appetizing mezzeh and signature grills with shisha, live entertainment, and broadcasts of sporting events for evenings well spent.","Middle Eastern, Smart Casual","Monday 05:00pm - 01:30am
Tuesday 05:00pm - 01:30am
Wednesday 05:00pm - 01:30am
Thursday 05:00pm - 01:30am
Friday 05:00pm - 01:30am
Saturday 05:00pm - 01:30am
Sunday 05:00pm - 01:30am",https://www.morecravings.com/en/menus/3
Vivo,https://cache.marriott.com/content/dam/marriott-renditions/CAIRZ/cairz-restaurant-0027-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0457960,31.2321630",+20225778899,rc.cairz.restaurants.reservations@ritzcarlton.com,https://www.morecravings.com/en/venues/vivo,"Vivo is our Italian restaurant in Cairo, offering Italian gastronomy in a contemporary setting overlooking a magical view of the Nile and Cairo Tower. Our chef has crafted a menu bursting with authentic flavors for a delectable dining experience.","Italian, Smart Casual","Monday 06:00pm - 11:30pm
Tuesday 06:00pm - 11:30pm
Wednesday 06:00pm - 11:30pm
Thursday 06:00pm - 11:30pm
Friday 06:00pm - 11:30pm
Saturday 06:00pm - 11:30pm
Sunday 06:00pm - 11:30pm
Monday 12:00pm - 04:00pm
Tuesday 12:00pm - 04:00pm
Wednesday 12:00pm - 04:00pm
Thursday 12:00pm - 04:00pm
Friday 12:00pm - 04:00pm
Saturday 12:00pm - 04:00pm
Sunday 12:00pm - 04:00pm",https://www.morecravings.com/en/menus/522
Culina,https://cache.marriott.com/content/dam/marriott-renditions/CAIRZ/cairz-restaurant-0023-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0457960,31.2321630",+20225778899,rc.cairz.restaurants.reservations@ritzcarlton.com,https://www.morecravings.com/en/venues/culina,"Our all-day dining restaurant prepares an array of international and local dishes, offering buffet-style for breakfast, lunch and dinner. Fridays host a Cairo favorite brunch. The weekly culinary event features live jazz and children’s entertainment.","International, Smart Casual","Monday 06:30am - 11:00am
Tuesday 06:30am - 11:00am
Wednesday 06:30am - 11:00am
Thursday 06:30am - 11:00am
Friday 06:30am - 11:00am
Saturday 06:30am - 11:00am
Sunday 06:30am - 11:00am
Monday 01:00pm - 04:00pm
Tuesday 01:00pm - 04:00pm
Wednesday 01:00pm - 04:00pm
Thursday 01:00pm - 04:00pm
Saturday 01:00pm - 04:00pm
Sunday 01:00pm - 04:00pm
Friday 01:00pm - 05:30pm",https://www.morecravings.com/en/menus/3352
"Roy's Smokehouse",https://cache.marriott.com/content/dam/marriott-renditions/CAIEG/caieg-kitchen-0201-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+20227394631,mhrs.caieg.fbreservation@marriott.com,https://www.morecravings.com/en/venues/roys-smokehouse,"Discover a variety of smoked lamb chops, briskets, short ribs, sausages all slowly cooked for over 12 hours for extra juiciness and tenderness at one of the best restaurants in Cairo at Roy’s Smokehouse. Don’t miss our sides, sandwiches and salads.","American, Casual","Monday 12:00pm - 12:00am
Tuesday 12:00pm - 12:00am
Wednesday 12:00pm - 12:00am
Thursday 12:00pm - 12:00am
Friday 12:00pm - 12:00am
Saturday 12:00pm - 12:00am
Sunday 12:00pm - 12:00am",N/A
"Ristorante Tuscany ",https://cache.marriott.com/content/dam/marriott-renditions/CAIEG/caieg-ristorante-0222-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+20227394631,mhrs.caieg.fbreservation@marriott.com,https://www.morecravings.com/en/venues/ristorante-tuscany-1,"Take in the aroma of freshly baked bread as you enter Ristorante Tuscany. From delectable homemade pasta like linguine to tortellini and more, you'll enjoy all your favorites at this Zamalek restaurant in an intimate setting.","Italian, Casual","Monday 06:00pm - 12:00am
Tuesday 06:00pm - 12:00am
Wednesday 06:00pm - 12:00am
Thursday 06:00pm - 12:00am
Friday 06:00pm - 12:00am
Saturday 06:00pm - 12:00am
Sunday 06:00pm - 12:00am",N/A
"The Egyptian Nights ",https://cache.marriott.com/content/dam/marriott-renditions/CAIEG/caieg-egyptian-0214-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+200227283000,mhrs.caieg.fbreservation@marriott.com,https://www.morecravings.com/en/venues/egyptian-nights,"Don't miss the opportunity to dine on some of the finest Middle Eastern cuisine, cooked right in front of you in Cairo at Egyptian Nights restaurant. Just follow the music that rings through our open-air location to sample some delicious local delicacies.","Other, Casual","Monday 06:00pm - 02:00am
Tuesday 06:00pm - 02:00am
Wednesday 06:00pm - 02:00am
Thursday 06:00pm - 02:00am
Friday 06:00pm - 02:00am
Saturday 06:00pm - 02:00am
Sunday 06:00pm - 02:00am",N/A
"Omar Khayyam Restaurant ",https://cache.marriott.com/content/dam/marriott-renditions/CAIEG/caieg-entrance-0252-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+20227394631,mhrs.caieg.fbreservation@marriott.com,https://www.morecravings.com/en/venues/omar-s-cafe,"Omar Khayyam is our main dining hotel restaurant where our guests can enjoy our signature breakfast buffet with a great variety of options whilst enjoying their indoor or outdoor garden setting. Open for a la carte lunch & dinner.","International, Casual","Monday 12:00am - 12:00am
Tuesday 12:00am - 12:00am
Wednesday 12:00am - 12:00am
Thursday 12:00am - 12:00am
Friday 12:00am - 12:00am
Saturday 12:00am - 12:00am
Sunday 12:00am - 12:00am",https://cache.marriott.com/is/image/marriotts7prod/caieg-garden-0257:Wide-Hor
"Garden Promenade Cafe",https://cache.marriott.com/content/dam/marriott-renditions/CAIEG/caieg-garden-0253-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+20227394631,Dalia.Mohamed@marriott.com,https://www.morecravings.com/en/venues/garden-promenade-caf,"Step into a secluded haven that is the Garden Promenade Café, located at the Palace gardens with spacious outdoor seating, a menu filled with Cairo Marriott’s specialties and refreshing beverages, a location that’s perfect for all seasons.","International, Casual","Monday 06:00am - 02:00am
Tuesday 06:00am - 02:00am
Wednesday 06:00am - 02:00am
Thursday 06:00am - 02:00am
Friday 06:00am - 02:00am
Saturday 06:00am - 02:00am
Sunday 06:00am - 02:00am",https://www.morecravings.com/en/menus/4758
"The Bakery ",https://cache.marriott.com/content/dam/marriott-renditions/CAIEG/caieg-bakery-0216-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+20227394631,mhrs.caieg.fbreservation@marriott.com,https://www.morecravings.com/en/venues/the-bakery,"Check out our delicious sandwiches, deli, bakery, salads, freshly brewed coffee, homemade cakes and chocolate at The Bakery. Order your breakfast, lunch or dinner as you dine amidst the palace arches with family or friends.",International,"Monday 12:00am - 12:00am
Tuesday 12:00am - 12:00am
Wednesday 12:00am - 12:00am
Thursday 12:00am - 12:00am
Friday 12:00am - 12:00am
Saturday 12:00am - 12:00am
Sunday 12:00am - 12:00am",https://cache.marriott.com/is/image/marriotts7prod/caieg-saraya-0255:Wide-Hor
"Saraya Gallery",https://cache.marriott.com/content/dam/marriott-renditions/CAIEG/caieg-saraya-0254-sq.jpg,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+20227394631,mhrs.caieg.fbreservation@marriott.com,https://www.morecravings.com/en/venues/saraya-gallery-1,"Indulge in fine dining in Cairo with French and international dishes at Saraya Gallery, nestled in the Palace's opulent heart. This refined venue offers a picturesque backdrop, ideal for a leisurely lunch or a serene, romantic dinner","French, Casual","Monday 12:00pm - 12:00am
Tuesday 12:00pm - 12:00am
Wednesday 12:00pm - 12:00am
Thursday 12:00pm - 12:00am
Friday 12:00pm - 12:00am
Saturday 12:00pm - 12:00am
Sunday 12:00pm - 12:00am",N/A
"Billiard Bar",https://cache.marriott.com/is/image/marriotts7prod/caieg-billiard-0202:Square,"https://www.google.com/maps/search/?api=1&query=30.0571620,31.2243140",+20227394631,mhrs.caieg.fbreservation@marriott.com,https://www.morecravings.com/en/venues/billiard-bar,"Unwind after a day on the go in Cairo at Billiard Bar. Our welcoming cigar lounge and restaurant is a comfortable place to relax with a crafted cocktail and fine dining. Enjoy the ambiance with its remarkable original palatial interiors and dimmed lights.","International, Casual","Monday 06:00pm - 02:00am
Tuesday 06:00pm - 02:00am
Wednesday 06:00pm - 02:00am
Thursday 06:00pm - 02:00am
Friday 06:00pm - 02:00am
Saturday 06:00pm - 02:00am
Sunday 06:00pm - 02:00am",https://cache.marriott.com/is/image/marriotts7prod/caieg-billiard-0202:Wide-Hor

```
---

## Author
Ali H. Radwan
GitHub: [@AliHRadwan](https://github.com/alihradwan)