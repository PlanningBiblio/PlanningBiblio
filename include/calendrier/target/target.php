<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <link rel="stylesheet" href="calendar.css" type="text/css" />
 <title>Calendar demo</title>
</head>

<body>
<p><strong><?php echo ((isset($_GET["date"]) && trim($_GET["date"]) != "")?("You clicked on the date ".$_GET["date"]):"No date selected"); ?></strong></p>
</body>
</html>
