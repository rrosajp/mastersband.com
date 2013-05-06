<?

    /**
     * minichat 2
     * Copyright Paul Mutton, 15th August 2002.
     * http://www.jibble.org/
     * 
     * Include this file on a PHP web page to add a mini chat box.
     *
     * Features:-
     *   Totally rewritten from scratch.
     *   Much much more efficient than the previous version.
     *   HTML tags are filtered out.
     *   Imposes a max word size to avoid wrapping issues.
     *   Max nick and message lengths are enforced on the server side.
     *   Accidental "refresh" reposting is avoided.
     *   Appends all messages chronologically to the archive file.
     *   Displays latest 20 messages with the most recent at the top.
     *   Now logs I.P. addresses within comments (do what you want with them).
     *   Displays posting time in correct local time.
     *   
     */

    $latest = $DOCUMENT_ROOT . "/minichat2.latest";
    $archive = $DOCUMENT_ROOT . "/minichat2.archive";
    $size = 20;
    $nick_size = 20;
    $message_size = 256;
    $max_word_size = 20;
    
?>

<table width="100%" border="0">
 <tr>
  <td width=75%>
   
   <p>
    <font face="arial,sans-serif" size="2">

<?
    
    // Check to see if the user is trying to post something.
    if (isset($minichat_md5) && isset($minichat_nick) && isset($minichat_message)) {
        
        // Replace any new line stuff with a space.
        $nick = strtr($nick, "\r\n", "  ");
        $message = strtr($message, "\r\n", "  ");

        // Trim leading and trailing whitespace where necessary and remove slashes.
        $nick = trim(stripslashes($minichat_nick));
        $message = trim(stripslashes($minichat_message));
        
        // Only proceed if the md5 hash of message is not repeated.
        if (md5($message) != $minichat_md5) {
        
            // Only proceed if the user actually filled in both fields.
            if (strlen($nick) > 0 && strlen($message) > 0) {
                
                // If the fields are too long, then chop them to the limits.
                if (strlen($nick) > $nick_size) {
                    $nick = substr($nick, 0, $nick_size);
                }
                if (strlen($message) > $message_size) {
                    $message = substr($message, 0, $message_size);
                }
                
                // Remove new line characters from the input.
                $nick = str_replace("\n", " ", $nick);
                $message = str_replace("\n", " ", $message);
                
                // Enforce the maximum word size by breaking up $message into lines.
                $message = preg_replace("/([^\s]{20})/", "$1\n", $message);
                
                // Now we can encode the nick and message into HTML.
	        $nick = htmlentities($nick);
                $message = htmlentities($message);
                
                // Now replace the new line characters in $message.
                $message = str_replace("\n", "<br>", $message);
                
                // The IP address of the poster, web cache or whatever.
                $ip = $_SERVER['REMOTE_ADDR'];
                $time = date("j M Y - G:i:s T");
                
                // Check to see if the 'latest' and 'archive' files exist and can be written to.
                if (!is_writable($latest) || !is_writable($archive)) {
                    // Touch both files.
                    touch($latest);
                    touch($archive);
                    if (!is_writable($latest) || !is_writable($archive)) {
                        exit("$latest or $archive is not writable. Please check your permissions and try again.");
                    }
                }
                
                // Read every line of the 'latest' file into an array.
                $lines = file($latest);
                $bottom_index = count($lines);
                
                // Note that each entry takes up 4 lines.
                $line_ip = "<!-- $ip -->\n";
                $line_nick = "* <font color=\"#ff6633\">$nick\n";
                $line_time = "</font><br>\n";
                $line_message = "$message<br><br>\n";

                $entry = $line_ip . $line_nick . $line_time. $line_message;

                $already_posted = 0;
                for ($i = 3; $i < $bottom_index; $i += 4) {
                    if ($lines[$i] == $line_message) {
                        $already_posted = 1;
                        break;
                    }
                }
                
                if ($already_posted == 0) {
                    // Now rebuild the 'latest' file.
                    // Start by entering the new entry at the top.
                    $out = fopen($latest, "w");
                    fwrite($out, $entry);
                    
                    // Then write all other entries except the oldest.
                    if ($bottom_index >= $size * 4) {
                        $bottom_index = $size * 4 - 4;
                    }
                    for ($i = 0; $i < $bottom_index; $i++) {
                        fwrite($out, $lines[$i]);
                    }
                    fclose($out);
                    
                    // Also append the entry to the archive file.
                    $out = fopen($archive, "a");
                    fwrite($out, $entry);
                    fclose($out);
                }
                else {
                    // This avoided a "probably accidental" repost.
                }
                
            }
            else {
                echo "<font color=\"red\">You must fill in both fields</font><br><br>";
            }
        }
        else {
            // This avoided a deliberate repost, maybe we should say something?
        }
        

    }
    
    // include the latest comments on the page.
    if (file_exists($latest)) {
        include($latest);
    }
    
?>

    </font>
   </p></td><td valign=top>
      <p>
    <form name="minichat_form" method="POST" action="<? echo $_SERVER['PHP_SELF']; ?>">
     <font face="arial,sans-serif" size="2">
      <input type="hidden" name="minichat_md5" value="<? if (isset($minichat_message)) {echo md5($minichat_message);} ?>">
      name: <input type="text" name="minichat_nick" maxlength="<? echo $nick_size; ?>" size="25" style="font-family: Verdana, Arial, Helvetica, Sans-serif; font-size: 10px"><br>
      &nbsp;msg: <textarea name="minichat_message" cols="25" rows="1" style="font-family: Verdana, Arial, Helvetica, Sans-serif; font-size: 10px"></textarea><br>
      <input type="submit" name="minichat_submit" value="post">
     </font>
    </form>
   </p>

  </td>
 </tr>
</table>