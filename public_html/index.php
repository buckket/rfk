<?php
 include 'inc/functions/general.php';
 include 'inc/config.php';
 include 'inc/header.php';
 
 if(!@$sq = $_GET["s"])
  $sq = "home";

 $page = get_content_vars($sq);

 if(@$page["HTTP-Header"]) {
  header($page["HTTP-Header"]);
 }

 if($page > 1 && is_int($page)) {
    $page = get_error(get_content_vars($sq));
 }


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">  
  <head>    
    <title>Radio freies Krautchan :: <?php echo $page["title"]; ?>    
    </title>    
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />    
    <link rel="stylesheet" href="style.css" type="text/css" media="screen,projection" />  
  </head>  
  <body>    
    <div id="container">      
      <div id="header">
        <img src="logo.png" alt="Logo" />      
      </div>      
      <div id="navigation">        
        <ul>             
          <li<?php if ($sq == "home") echo " class='selected'"; ?>>          
          <a href="?s=home">&Uuml;bersicht</a>          
          </li>          
          <li<?php if ($sq == "history") echo " class='selected'"; ?>>          
          <a href="?s=history">Verlauf</a>          
          </li>          
          <li<?php if ($sq == "broadcasts") echo " class='selected'"; ?>>          
          <a href="?s=broadcasts">Sendungen</a>          
          </li>          
          <li<?php if ($sq == "status") echo " class='selected'"; ?>>          
          <a href="?s=status">Status</a>          
          </li>          
          <?php
           if(!isUser()) {
          ?>
          <li<?php if ($sq == "login") echo " class='selected'"; ?>>          
          <a href="?s=login">Anmelden</a>          
          </li>        
          <li<?php if ($sq == "register") echo " class='selected'"; ?>>
          <a href="?s=register">Registrieren</a>
          </li>
          <?php
           }
          ?>
          <li style="float: right;">
          <p class="userinfo">User: <?php echo get_current_login(); ?></p>
          </li>
        </ul>      
      </div>             
      <?php echo $page["content"]; ?>
      <?php
       if (isUser()) {
      ?>
      <div id="subcontent">        
        <h2>Benutzermen&uuml;</h2>        
        <ul class="menublock">             
          <li>          
          <a href="?s=settings">Einstellungen</a>          
          </li>             
          <li>          
          <a href="?s=logout">Logout</a> 
          </li>
          <li>
          <a href="?s=help">Hilfe</a>              
          <?php
           if(isAdmin()) {
          ?>
          <ul>	               
            <li>            
            <a href="#">Sub 1</a>            
            </li>                   
            <li>            
            <a href="#">Sub 2</a>            
            </li>	           
          </ul>          
          <?php
           }
          
          ?>
        </li>
        </ul>
      </div>
      <?php
       }
      ?>      
      <div id="footer">        
        <p>
        <?php 
        
        $stop = strtok(microtime(), " ") + strtok(" ");
        $time = number_format($stop - $start, 6);
        
        ?>            
          Generated in <?=$time?>s | <img src="xhtml_valid.png" class="vi" alt="valid xhtml" /><img src="css_valid.png" class="vi" alt="valid css" />        
        </p>      
      </div>    
    </div>  
  </body>
</html>
<?php
 include 'inc/footer.php';

?>
