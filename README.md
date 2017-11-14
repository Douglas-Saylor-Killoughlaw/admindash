# admindash: a bunch of web and console scripts for Cloudflare firewall operations and webserver logs quick analysis
This is a set of simple utilities, written in PHP, for \*nix platforms, which make the life of website admin easier.
This is basically a web interface for quick generation of long CLI commands like:

`cd /var/www/website/data/logs/ 2>&1; find -newermt '2017-11-12 00:00' -not -newermt '2017-11-14 23:59' -exec zgrep -m 5000 '80.68.0.250' \{\} \;  | grep -E -v '\.css|\.js|\.svg|\.jpg|\.png|\.gif|\.jpeg'   2>&1
`


which answer day-to-day website admin questions: `which web pages the 80.68.0.250 visited from 12 to 14 Nov, excluding png and gif and css requests?` or `which ips hit my apache most often?` or  `what are the top ips visiting page /article/my-article?`

I've started admindash when I realized I am copypasting all these useful commands from my notepad to terminal multiple times per day. After I used admindash for a while, and liked it, I implemented additional fancy features like ip location resolving (via SXgeo) and simple Cloudflare firewall integration, and website username-to-ip resolving as well.


# Installation
- Clone to www folder of your website
- Copy config.php.dist to config.php and edit variables inside.
- Make sure your apache logs folder is readable by the script:
  - change 640 permission to 644 in your logrotate config `/etc/logrotate.d/apache2` or `/etc/logrotate.d/httpd`
  - do `chmod -R a+r /var/log/apache2/` to make already created log files readable
- Access as http://yourwebsite.com/admindash/
- Access CLI by executing php /admindash/cli.php
*Warning: no authorization is implemented yet, just very basic ip-based check! Remove it and replace it with apache htpasswd or similar auth!*

# Features
- Cloudflare integration with quick ban form
- Quick search for your logrotated gzipped logs
- Integration with SXgeo to see ips locations
- Optional integration with your website DB to map your users to ips
- CLI utlitiy to ban ips (cli.php) in case your web server is already unresponsive because of DDoS attack

