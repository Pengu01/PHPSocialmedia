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
        $db->exec("CREATE TABLE IF NOT EXISTS messages(content text, timestamp text, userid textt, followeronly boolean);");
        //Create table for users
        $db->exec("CREATE TABLE IF NOT EXISTS users(username text primary key, email text unique, password text);");
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
            $db->exec("insert into messages values (\"" . $_POST["content"] . "\",\"". time() . "\", \"" . $_POST["userid"] . "\",\"". $_POST["followeronly"] . "\");");
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
        <div style="overflow-y: scroll; word-wrap: break-word; height:60vh">
        <?php
        //If you are logged in 
        if(isset($_COOKIE["userid"]))
        {
            //takes all the messages as well as the username by joining on id
            $allInputQuery = "SELECT messages.content, messages.followeronly, users.username, messages.timestamp FROM messages JOIN users ON messages.userid = users.rowid ORDER BY messages.timestamp DESC"; 
            $messages = $db->query($allInputQuery); 
            echo "<hr>";
            //displays the messages inside the box
            while($row = $messages->fetchArray(SQLITE3_ASSOC))
            {
                echo "<b>" . $row['username'] . ":</b> " . $row['content'] . "<br>";
                echo "<small>" . date("Y-m-d H:i",$row['timestamp']) . "</small>";
                echo "<hr>";
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
            <input type=\"submit\" value=\"Post\">
            </form>";
            //logout button
            echo "<form method=\"post\" action=\"#\">
            Logout:<input type=\"submit\" value=\"Logout\" name=\"logout\">
            </form>";
        }
    ?>
</body>
</html>