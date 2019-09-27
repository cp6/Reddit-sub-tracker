# Subreddit tracker
Tracks information and details for posts submitted to a user defined Reddit subreddit.

The below example will fetch data for the [NBA subreddit](https://reddit.com/r/nba). Simply point a cron job for every minute to fetch the latest posts.
```php
require_once('rdt-track.php');//Class and functions

$call = new tracker();//New instance
$call->track('nba', 25);//NBA sub, latest 25 posts
$call->insertData(1);//Insert new data and if post already exists do an update of upvotes/comments
```

### Note 
Ensure you run `database.sql` into your database and you configure `db_connect()` at line 2 of `rdt-track.php` to have your MySQL connection details.

#### Information stored

* Post id
* Post title
* Post domain
* Post url
* Post media url
* Posters username
* Subreddit
* Post upvotes
* Post comments
* Post comments
* Post submitted datetime
* Post status (has it been deleted or removed?)
* Post self text (if it is selftext)
* Post thumbnail