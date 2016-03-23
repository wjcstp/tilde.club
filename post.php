<?php
/*
a little script to manage blog entries for my tilde club page
goals:  write individual posts as txt files
        publish a draft by copying it to a blog folder and updating the index
        use a common template for posts
        update index page with latest post & links to archive

FUNCTIONS
    publish: read draft file, render blog post to full html page, write to blog folder
    index: render and write index page with latest blog post
    drafts: list all drafts, define newest entry, define new archive list
    write: needs folder, filename, data
    
TODO
    archive array has all entries, but for some reason not going to list or page loop
    put archive list into file rendering
    past drafts not rendering to blog
    
*/

#global $argv;

#if ($argv[1]) {
#    $draft = $argv[1];
#    } else {
#    echo "add the path to the draft\r\n";
#}    

#publish($draft);
#archive();

date_default_timezone_set('UTC');


index();


function index() {
    // render new index page with header/footer
    $newfile = file_get_contents("./templates/header.inc");
    
    list ($current, $archives, $full_list) = drafts();

    # add archive listing    

    $archivelist = archivelist($archives);
    $newfile .= $archivelist;

    # add latest entry here
    $newfile .= "<div id=\"journal\">\r\n".
    file_get_contents("./drafts/".$current)
    ."</div>\r\n";
    
    $newfile .= file_get_contents("./templates/footer.inc");
    
    write(".","index.html",$newfile);
    publish($current, $archives, $archivelist);
    rss($full_list);

}


function archivelist($archives) {
    // create archive list
    $archivelist = "<div id='archives'>
    <h3>Past entries</h3>\r<ul>";
    foreach ($archives as $archive) {
        $title = fgets(fopen('drafts/'.$archive, 'r'));
        $title = preg_replace ( "/<\/?h3>/" , "" , $title);
        $archivelist .= "<li><a href=\"/~wjc/blog/".$archive."\">".$title."</a></li>\r\n";
        }
    $archivelist .= "</ul>\r</div>";
    return $archivelist;

}


function drafts() {
    // list items in drafts folder, set variables for current list, latest post
    // assume we only need to publish newest draft
    $dir = "./drafts";
    $dh  = opendir($dir);
    while (false !== ($filename = readdir($dh))) {
        if (strpos($filename, ".html")) {
        $files[] = $filename;
#        print_r($files);
        }
    }
    rsort($files);
#    print_r($files);
    // set newest as current post, and remove from archive list
    $full_list = $files;
    $current = array_shift($files);
    
    return array ($current, $files, $full_list);
}


function publish($current, $archives, $archivelist) {
    // render a single blog page, write to /blog dir, add header/footer, nav links
    // works!
    
    foreach ($archives as $archive) {
        $newfile="";
        $newfile .= file_get_contents("./templates/blogheader.inc");
        $newfile .= $archivelist;
        $newfile .= "<div id=\"journal\">\r\n".file_get_contents("./drafts/".$archive)."</div>\r\n";
        $newfile .= file_get_contents("./templates/blogfooter.inc");
        write("blog",$archive,$newfile);
        echo "done: ". $archive."\r\n";
        }
}


function write($folder,$filename,$contents) {
    // works!
    $fp = fopen($folder."/".$filename, 'w');
    fwrite($fp, $contents);
    fclose($fp);
}

function rss($full_list) {

    $rssfeed = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
    <rss version=\"2.0\">
    <channel>
     <title>Bill Connell's Project Pages</title>
     <description>Updates and documentations for things i make or fix or break and maybe fix.</description>
     <link>http://tilde.club/~wjc/index.html</link>
     <pubDate>".date(DATE_RFC2822)."</pubDate>
    ";

    foreach ($full_list as $item) {

    $current_title = fgets(fopen('drafts/'.$item, 'r'));
    $current_title = preg_replace ( "/<\/?h3>/" , "" , $current_title);

    $rssfeed .= "<item>
      <title>".$current_title."</title>
      <description>".preg_replace('/^.+\n/', '', file_get_contents("./drafts/".$item))
."</description>
      <link>http://tilde.club/~wjc/blog/".$item."</link>
      <guid>http://tilde.club/~wjc/blog/".$item."</guid>
      <pubDate>".date(DATE_RFC2822)."</pubDate>
     </item>
      ";
    }

    $rssfeed .= "</channel>
    </rss>";
    write(".","rss.xml",$rssfeed);

}


?>