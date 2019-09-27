<?php
function db_connect()
{
    $host = 'localhost';
    $db_name = '';
    $db_user = '';
    $db_password = '';
    $db = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
    return new PDO($db, $db_user, $db_password, $options);
}

class tracker
{
    private $sub;
    private $data;

    /**
     * Sets subreddit and gets data
     * @param string $sub
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function track($sub, $limit = 50)
    {
        if (!isset($sub) or trim($sub) == '') {
            throw new Exception("You must provide a Reddit sub");
        }
        $this->sub = $sub;
        $url = @file_get_contents("https://www.reddit.com/r/$sub/new.json?sort=new&limit=$limit");
        if (strpos($http_response_header[0], "403") || strpos($http_response_header[0], "404")) {
            throw new Exception("Denied access");
        } else {
            $data = json_decode($url, true);
            $this->data = $data;
            return $data;
        }
    }

    /**
     * Inserts and optional updates the data with the Mysql DB
     * @param int $do_update
     * @return boolean
     */
    public function insertData($do_update = 0)
    {
        $data = $this->data;
        foreach ($data['data']['children'] as $val) {
            $pid = $val['data']['id'];
            $post_title = clean_title($val['data']['title']);
            $media_domain = $val['data']['domain'];
            $subreddit = $val['data']['subreddit'];
            $media_url = $val['data']['url'];
            $date_format = gmdate('Y-m-d H:i:s', $val['data']['created_utc']);
            $username = $val['data']['author'];
            $flair = $val['data']['author_flair_text'];
            $reddit_link = $val['data']['permalink'];
            $over_18 = $val['data']['over_18'];
            if ($over_18 == 'true') {
                $nsfw = 1;
            } else {
                $nsfw = 0;
            }
            $is_self = $val['data']['is_self'];
            if ($is_self == 'true') {
                $self = 1;
            } else {
                $self = 0;
            }
            $thumbnail = $val['data']['thumbnail'];
            $self_text = $val['data']['selftext'];
            if (isset($val['data']['secure_media']['oembed']['thumbnail_url'])) {
                $thumbnail = substr($val['data']['secure_media']['oembed']['thumbnail_url'], 0, strpos($val['data']['secure_media']['oembed']['thumbnail_url'], ".jpg"));
            }
            $db = db_connect();
            if ($do_update == 1) {
                $select = $db->prepare("SELECT `pid` FROM `status` WHERE `pid` = :pid;");
                $select->execute(array(':pid' => $pid));
                $row = $select->fetch();
                if ($row > 0) {
                    $comments = $val['data']['num_comments'];
                    $ups = $val['data']['ups'];
                    $update = $db->prepare("UPDATE `status` SET `checked` = '1', `ups` = :ups, `comments` = :comments WHERE `pid` = :pid");
                    $update->execute(array(':ups' => $ups, ':comments' => $comments, ':pid' => $pid));
                }
            }
            $insert_post = $db->prepare('INSERT IGNORE INTO `posts` (`pid`, `title`, `domain`, `media_url`, `sub`, `datetime`) VALUES (?, ?, ?, ?, ?, ?)');
            $insert_post->execute(["$pid", "$post_title", "$media_domain", "$media_url", "$subreddit", "$date_format"]);
            $insert_details = $db->prepare('INSERT IGNORE INTO `details` (`pid`, `user`, `flair`, `link`, `nsfw`, `thumbnail`) VALUES (?, ?, ?, ?, ?, ?)');
            $insert_details->execute(["$pid", "$username", "$flair", "$reddit_link", "$nsfw", "$thumbnail"]);
            $insert_status = $db->prepare('INSERT IGNORE INTO `status` (`pid`, `result`, `ups`, `comments`, `checked`, `updated`) VALUES (?, ?, ?, ?, ?, ?)');
            $insert_status->execute(["$pid", "0", "0", "0", "0", "$date_format"]);
            if ($self == 1) {
                $insert_st = $db->prepare('INSERT IGNORE INTO `self_text` (`pid`, `text`) VALUES (?, ?)');
                $insert_st->execute(["$pid", clean_title($self_text)]);
            }
        }
        return true;
    }

    /**
     * Updates comment and upvote count, checks deleted/removed.
     * @param string $pid
     * @return boolean
     */
    public function updatePost($pid)
    {
        $sub = $this->sub;
        $db = db_connect();
        $data = @file_get_contents("https://www.reddit.com/r/$sub/$pid.json");
        if (strpos($http_response_header[0], "403") || strpos($http_response_header[0], "404")) {
            $update = $db->prepare("UPDATE `status` SET `result` = '3', `checked` = '1' WHERE `pid` = :pid");
            $update->execute(array(':pid' => $pid));
            return false;
        } else {
            $data = json_decode($data, true);
            $comments = $data[0]['data']['children'][0]['data']['num_comments'];
            $ups = $data[0]['data']['children'][0]['data']['ups'];
            $st = $data[0]['data']['children'][0]['data']['selftext'];
            if ($st == '[deleted]') {
                $update = $db->prepare("UPDATE `status` SET `result` = '1', `checked` = '1', `ups` = :ups, `comments` = :comments WHERE `pid` = :pid");
                $update->execute(array(':ups' => $ups, ':comments' => $comments, ':pid' => $pid));
            } elseif ($st == '[removed]') {
                $update = $db->prepare("UPDATE `status` SET `result` = '2', `checked` = '1', `ups` = :ups, `comments` = :comments WHERE `pid` = :pid");
                $update->execute(array(':ups' => $ups, ':comments' => $comments, ':pid' => $pid));
            } else {
                $update = $db->prepare("UPDATE `status` SET `checked` = '1', `ups` = :ups, `comments` = :comments WHERE `pid` = :pid");
                $update->execute(array(':ups' => $ups, ':comments' => $comments, ':pid' => $pid));
            }
        }
        return true;
    }
}

function clean_title($string)//Makes title sanitized
{
    $string = str_replace("'", '', $string);
    return preg_replace('/[^A-Za-z0-9\-]/', ' ', $string);// Removes special chars.
}

function result_string($result)//Returns result type as readable string
{
    if ($result == 0) {
        return "Normal";
    } elseif ($result == 1) {
        return "Deleted";
    } elseif ($result == 2) {
        return "Removed";
    } elseif ($result == 3) {
        return "Error";
    } else {
        return "No string for $result";
    }
}