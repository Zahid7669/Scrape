<?php

// Function to get the title of a web page
function get_title($url) {
    try {
        $html = file_get_contents($url);
        $dom = new DOMDocument;
        @$dom->loadHTML($html);
        $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
        echo "$title <br>";
        return $title ? trim($title) : null;
    } catch (Exception $e) {
        echo "Error fetching title from $url: " . $e->getMessage();
        return null;
    }
}

// Function to insert a page into the 'urls' table
function insert_url($conn, $url, $title) {
    $stmt = $conn->prepare("INSERT INTO urls (url, title) VALUES (?, ?)");
    $stmt->bind_param("ss", $url, $title);
    $stmt->execute();
    return $conn->insert_id;
}

// Function to insert a keyword into the 'keywords' table
function insert_keyword($conn, $keyword) {
    $stmt = $conn->prepare("INSERT INTO keywords (keyword) VALUES (?)");
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    return $conn->insert_id;
}

// Function to insert a connection between a page and a keyword into the 'page_keywords' table
function insert_page_keyword($conn, $url_id, $keyword_id) {
    $stmt = $conn->prepare("INSERT INTO page_keywords (url_id, keyword_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $url_id, $keyword_id);
    $stmt->execute();
}

// Connect to MySQL
$servername = "localhost"; // Change this to your MySQL server address if it's not on the same machine
$username = "root";
$password = "";
$dbname = "random";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// List of web page URLs
$urls = [
    "https://www.wikipedia.org/",
    "https://www.nytimes.com/",
    "https://www.bbc.com/",
    "https://www.github.com/",
    "https://www.cnn.com/"
];

// Process each web page
foreach ($urls as $url) {
    $title = get_title($url);
    if ($title) {
        $url_id = insert_url($conn, $url, $title);

        // Split title into keywords
        $keywords = explode(" ", $title);

        // Process each keyword
        foreach ($keywords as $keyword) {
            $keyword = strtolower($keyword); // Convert to lowercase for consistency
            $keyword_id = insert_keyword($conn, $keyword);
            insert_page_keyword($conn, $url_id, $keyword_id);
        }
    }
}

// Close the connection
$conn->close();

?>
