<?php
include_once "templates/base.php";
if (!isWebRequest()) {
  echo "To view this page on a webserver using PHP 5.4 or above run: \n\t
    php -S localhost:8080\n";
  exit();
}
echo pageHeader("PHP Library Examples"); ?>
<ul>
  <li><a href="simple-query.php">A query using simple API access</a></li>
  <li><a href="user-example.php">A query for user data, using OAuth 2.0 authentication.</a></li>
  <li><a href="batch.php">An example of combining multiple calls into a batch request</a></li>
  <li><a href="service-account.php">A query using the service account functionality.</a></li>
  <li><a href="simplefileupload.php">An example of a small file upload.</a></li>
  <li><a href="fileupload.php">An example of a large file upload.</a></li>
  <li><a href="idtoken.php">An example of verifying and retrieving the id token.</a></li>
  <li><a href="multi-api.php">An example of using multiple APIs.</a></li>
</ul>


<ul>
  <li><a href="https://developers.google.com/fusiontables" target="_blank">Documentation</a></li>
  <li><a href="../src/Google/Service/Fusiontables.php">Fusion tables</a></li>
  <li><a href="../Custom_work/test1.php">test 1</a></li>
  <li><a href="../Custom_work/test2.php">test 2</a></li>
  <li><a href="../Custom_work/test3.php">test 3</a></li>
  
</ul>

<?php echo pageFooter();
