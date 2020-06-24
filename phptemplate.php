<?
$default = 'home';
$page = isset($_GET['p']) ? $_GET['p'] : $default;

$page = basename($page);

if (!file_exists($page.'.php'))    
	{ 
    $page = $default;
	} 
$title = ucwords($page);
?>