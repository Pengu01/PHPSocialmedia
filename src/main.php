<!DOCTYPE html>
<html>
<head>
    <title>Chrip</title>
</head>
<body>
    <h1>Chrip</h1>
    <?php
        // Establish database connection
        $db = new SQLite3('question.sq3');
        //Create table for messages
        $db->exec("CREATE TABLE IF NOT EXISTS messages(content text, timestamp text, userid textt, followeronly boolean, direct int);");
        //Create table for users
        $db->exec("CREATE TABLE IF NOT EXISTS users(username text primary key, email text unique, password text, followlist text);");
        //checks if user wants to logout
        if(isset($_POST["logout"]))
        {
            //removes the cookie by setting time to 600 seconds ago
            setcookie("userid", 0, time() - (600));
            //refreshing site to get the changes
            header("Refresh:0");
        }
        //if user tries to log in
        if(isset($_POST["name"]) && isset($_POST["password"]))
        {
            //gets all the usernames and passwords on the users table
            $allInputQuery = "SELECT users.username, users.password, rowid FROM users"; 
            $users = $db->query($allInputQuery); 
            while($row = $users->fetchArray(SQLITE3_ASSOC))
            {
                //checks if name is equal
                if($_POST["name"] == $row["username"]) 
                {
                    //checks if password associated with name is equal
                    if($_POST["password"] == $row["password"]) 
                    {
                        //sets cookie so user is logged in for 10 minutes
                        setcookie("userid", $row["rowid"], time() + (600));
                        //refreshes for changes
                        header("Refresh:0");
                    }
                }
            }
        }
        //if user wants to register
        if(isset($_POST["regname"]) && isset($_POST["regmail"]) && isset($_POST["regpassword"]))
        {
            //inserts user info into table
            $db->exec("insert into users(username,email,password) values (\"" . $_POST["regname"] . "\",\"". $_POST["regmail"] . "\", \"" . $_POST["regpassword"] . "\");");
        }
        //if user want to send message
        if(isset($_POST["content"]) && isset($_POST["userid"]))
        {
            //inserts message info into table
            $words = array("Bord", "Stol", "B0rd", "St0l", "St01", "Sto1");
            $content = $_POST["content"];
            foreach ($words as $word) 
            {
                $content = str_ireplace($word, str_repeat("*",strlen($word)),  $content);
            }
            $db->exec("insert into messages values (\"" . $content . "\",\"". time() . "\", \"" . $_POST["userid"] . "\",\"". $_POST["followeronly"] . "\",\"". $_POST["direct"] . "\");");
        }
        //if user wants to follow
        if(isset($_POST["followuser"]))
        {
            //takes the id of the account the user wants to follow
            $allInputQuery = "SELECT users.rowid FROM users where users.username = \"".$_POST["followuser"]."\""; 
            $rowidd = $db->query($allInputQuery); 
            while($row = $rowidd->fetchArray(SQLITE3_ASSOC))
            {
                $rowid = $row['rowid'];
            }
            //gets the users followlist
            $allInputQuery = "SELECT users.followlist FROM users where users.rowid = \"".$_COOKIE["userid"]."\""; 
            $rowidd = $db->query($allInputQuery); 
            while($row = $rowidd->fetchArray(SQLITE3_ASSOC))
            {
                $followlist = $row['followlist'];
            }
            //makes it into an array
            $tempfollowlist = explode("-",$followlist);
            //checks if user is already following or they dont exist
            if(isset($rowid) && !in_array($rowid, $tempfollowlist))
            {
                //adds the user into the follow list
                $db->exec("update users set followlist = \"". $followlist . "-". $rowid . "\" where users.rowid = \"".$_COOKIE["userid"]."\"");
            }
        }
        //if user wants to unfollow
        if(isset($_POST["unfollowuser"]))
        {
            //finds users followlist
            $allInputQuery = "SELECT users.followlist FROM users where users.rowid = \"".$_COOKIE["userid"]."\""; 
            $rowidd = $db->query($allInputQuery); 
            while($row = $rowidd->fetchArray(SQLITE3_ASSOC))
            {
                $followlist = $row['followlist'];
            }
            //makes it into an array
            $tempfollowlist = explode("-",$followlist);
            foreach($tempfollowlist as $userid)
            {
                //ckecks if it is the account that the user wants to unfollow
                if($_POST["unfollowuser"] != $userid)
                {
                    //if not it gets added back
                    $sfollowlist = "-".$userid;
                }
            }
            //adds all the accounts the user did not want to unfollow back
            $db->exec("update users set followlist = \"".$sfollowlist."\" where users.rowid = \"".$_COOKIE["userid"]."\"");
        }
        //if user is not logged in and does not want to register
        if(!isset($_COOKIE["userid"]) && !isset($_POST["register"]))
        {
            //shows login form
            echo "<h3>Login:</h3>
            <form method=\"post\" action=\"#\">
            Name: <input type=\"text\" name=\"name\"><br>
            Password: <input type=\"text\" name=\"password\"> <br>
            <input type=\"submit\" value=\"Login\">
            </form><br>";
            //register button
            echo "<form method=\"post\" action=\"#\"> I want to register:<input type=\"submit\" value=\"Register\" name=\"register\">
            </form>";
        }
        //if user wants to register
        if(isset($_POST["register"]))
        {
            //register form
            echo "<h3>Register:</h3>
            <form method=\"post\" action=\"#\">
            Name: <input type=\"text\" name=\"regname\"><br>
            Email: <input type=\"text\" name=\"regmail\"><br>
            Password: <input type=\"text\" name=\"regpassword\"> <br>
            <input type=\"submit\" value=\"Register\">
            </form>";
            //login button
            echo "<form method=\"post\" action=\"#\"> I want to login:<input type=\"submit\" value=\"Login\">
            </form>";
        }
        ?>
        <!-- this makes the message window not go forever, instead it limits it to a box and adds a scroll so that you can browse them without taking up the entire screen -->
        <div style="overflow-y: scroll; word-wrap: break-word; height:50vh">
        <?php
        //If you are logged in 
        if(isset($_COOKIE["userid"]))
        {
            //gets the users followerlist
            $allInputQuery = "SELECT users.followlist FROM users where users.rowid = \"".$_COOKIE["userid"]."\""; 
            $followsss = $db->query($allInputQuery); 
            while($row = $followsss->fetchArray(SQLITE3_ASSOC))
            {
                $followss = $row['followlist'];
            }
            //makes it into an array
            $follows = explode("-",$followss);
            //takes all the messages as well as the username by joining on id
            $allInputQuery = "SELECT messages.content, messages.followeronly, users.username, messages.timestamp, users.rowid, messages.direct FROM messages JOIN users ON messages.userid = users.rowid ORDER BY messages.timestamp DESC"; 
            $messages = $db->query($allInputQuery); 
            echo "<hr>";
            //displays the messages inside the box
            while($row = $messages->fetchArray(SQLITE3_ASSOC))
            {
                //checks if they are allowed to read it
                if($row['rowid'] == $_COOKIE["userid"])
                {
                    //you sent it as a direct message
                    if($row['direct'] != '-1')
                    {
                        $message = $db->query("select users.username from users where rowid = '". $row['direct'] ."'"); 
                        echo "<b>" . $row['username'] . " -> ". $message->fetchArray(SQLITE3_ASSOC)['username'] . ":</b> " . $row['content'] . "<br>";
                        echo "<small>" . date("Y-m-d H:i",$row['timestamp']) . "</small>";
                        echo "<hr>";
                        continue;
                    }
                    //you sent it as a followers only message
                    if($row['followeronly'] != "off")
                    {
                        echo "🔒 <b>" . $row['username'] . ":</b> " . $row['content'] . "<br>";
                        echo "<small>" . date("Y-m-d H:i",$row['timestamp']) . "</small>";
                        echo "<hr>";
                        continue;
                    }
                    //you sent it as a normal message
                    echo "<b>" . $row['username'] . ":</b> " . $row['content'] . "<br>";
                    echo "<small>" . date("Y-m-d H:i",$row['timestamp']) . "</small>";
                    echo "<hr>";
                    continue;
                }
                if($row['followeronly'] == "off" && $row['direct'] == '-1')
                {
                    //everyone can see it
                    echo "<b>" . $row['username'] . ":</b> " . $row['content'] . "<br>";
                    echo "<small>" . date("Y-m-d H:i",$row['timestamp']) . "</small>";
                    echo "<hr>";
                    continue;
                }
                if(in_array($row['rowid'], $follows) && $row['direct'] == '-1')
                {
                    //following only message
                    echo "🔒 <b>" . $row['username'] . ":</b> " . $row['content'] . "<br>";
                    echo "<small>" . date("Y-m-d H:i",$row['timestamp']) . "</small>";
                    echo "<hr>";
                    continue;
                }
                if($row['direct'] == $_COOKIE['userid'])
                {
                    //direct message
                    $message = $db->query("select users.username from users where rowid = '". $row['direct'] ."'"); 
                    echo "<b>" . $row['username'] . " -> ". $message->fetchArray(SQLITE3_ASSOC)['username'] . ":</b> " . $row['content'] . "<br>";
                    echo "<small>" . date("Y-m-d H:i",$row['timestamp']) . "</small>";
                    echo "<hr>";
                    continue;
                }
            }
        }
    ?>
    </div>
    <?php
    //if user is logged in
        if(isset($_COOKIE["userid"]))
        {
            //show message form
            echo "<h3>Chrip a message:</h3>
            <form method=\"post\" action=\"#\">
            <textarea name=\"content\" rows=\"4\" cols=\"50\"></textarea><br>
            <input type=\"hidden\" name=\"userid\" value=\"" . $_COOKIE["userid"] . "\">
            <input type=\"hidden\" name=\"followeronly\" value=\"off\">
            Follower only<input type=\"checkbox\" name=\"followeronly\">
            <select name=\"direct\">
            <option value=\"-1\">All</option>";
            $allInputQuery = "SELECT users.username, users.rowid FROM users"; 
            $usernamelist = $db->query($allInputQuery); 
            while($row = $usernamelist->fetchArray(SQLITE3_ASSOC))
            {
                //shows them as dropdown menu with id as value
                echo "<option value=\"". $row['rowid'] . "\">". $row['username'] ."</option>";
            }
            echo "</select>
            <input type=\"submit\" value=\"Post\">
            </form><br>";
            //logout button
            echo "<form method=\"post\" action=\"#\">
            Logout:<input type=\"submit\" value=\"Logout\" name=\"logout\">
            </form><br>";
            echo "Follow a user:
            <form method=\"post\" action=\"#\">
            <input type=\"text\" name=\"followuser\">
            <input type=\"submit\" value=\"Follow\">
            </form><br>";
            //gets users follower list
            $allInputQuery = "SELECT users.followlist FROM users where users.rowid = \"".$_COOKIE["userid"]."\""; 
            $followsss = $db->query($allInputQuery); 
            while($row = $followsss->fetchArray(SQLITE3_ASSOC))
            {
                $followss = $row['followlist'];
            }
            //makes it into an array and cleans it up
            $follows = explode("-",$followss);
            $follows = array_filter($follows);
            //if you are following more than 0
            if(count($follows) > 0)
            {
                //shot unfollow select screen
                echo "Unfollow a user:
                <form method=\"post\" action=\"#\">
                <select name=\"unfollowuser\">";
                //displays all followin in dropdown
                foreach($follows as $unfollows)
                {
                    //gets their name from id
                    $allInputQuery = "SELECT users.username FROM users where users.rowid = \"".$unfollows."\""; 
                    $usernamelist = $db->query($allInputQuery); 
                    while($row = $usernamelist->fetchArray(SQLITE3_ASSOC))
                    {
                        //shows them as dropdown menu with id as value
                        echo "<option value=\"". $unfollows . "\">". $row['username'] ."</option>";
                    }
                }   
                //submit button5
                echo "</select>
                <input type=\"submit\" value=\"Unfollow\">
                </form>";
            }
        }
    ?>
</body>
</html>