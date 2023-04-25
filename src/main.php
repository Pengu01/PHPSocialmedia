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
        $db->exec("CREATE TABLE IF NOT EXISTS messages(content text, timestamp text, userid text);");
        //Create table for users
        $db->exec("CREATE TABLE IF NOT EXISTS users(username text primary key, email text, password text);");
        if(isset($_POST["logout"]))
        {
            setcookie("userid", 0, time() - (600));
            header("Refresh:0");
        }
        if(isset($_POST["name"]) && isset($_POST["password"]))
        {
            $allInputQuery = "SELECT users.username, users.password, rowid FROM users"; 
            $users = $db->query($allInputQuery); 
            while($row = $users->fetchArray(SQLITE3_ASSOC))
            {
                if($_POST["name"] == $row["username"]) 
                {
                    if($_POST["password"] == $row["password"]) 
                    {
                        setcookie("userid", $row["rowid"], time() + (600));
                        header("Refresh:0");
                    }
                }
            }
        }
        if(isset($_POST["regname"]) && isset($_POST["regmail"]) && isset($_POST["regpassword"]))
        {
            $db->exec("insert into users(username,email,password) values (\"" . $_POST["regname"] . "\",\"". $_POST["regmail"] . "\", \"" . $_POST["regpassword"] . "\");");
        }
        if(isset($_POST["content"]) && isset($_POST["userid"]))
        {
            $db->exec("insert into messages values (\"" . $_POST["content"] . "\",\"". time() . "\", \"" . $_POST["userid"] . "\");");
        }
        if(!isset($_COOKIE["userid"]) && !isset($_POST["register"]))
        {
            echo "<h3>Login:</h3>
            <form method=\"post\" action=\"#\">
            Name: <input type=\"text\" name=\"name\"><br>
            Password: <input type=\"text\" name=\"password\"> <br>
            <input type=\"submit\" value=\"Login\">
            </form><br>";
            echo "<form method=\"post\" action=\"#\"> I want to register:<input type=\"submit\" value=\"Register\" name=\"register\">
            </form>";
        }
        if(isset($_POST["register"]))
        {
            echo "<h3>Register:</h3>
            <form method=\"post\" action=\"#\">
            Name: <input type=\"text\" name=\"regname\"><br>
            Email: <input type=\"text\" name=\"regmail\"><br>
            Password: <input type=\"text\" name=\"regpassword\"> <br>
            <input type=\"submit\" value=\"Register\">
            </form>";
            echo "<form method=\"post\" action=\"#\"> I want to login:<input type=\"submit\" value=\"Login\">
            </form>";
        }
        ?>
        <div style="overflow-y: scroll; word-wrap: break-word; height:60vh">
        <?php
        //ifall du skrivit in en text
        if(isset($_COOKIE["userid"]))
        {
            // Retrieve and display messages from the database
            $allInputQuery = "SELECT messages.content, users.username, messages.timestamp FROM messages JOIN users ON messages.userid = users.rowid ORDER BY messages.timestamp DESC"; 
            $messages = $db->query($allInputQuery); 
            echo "<hr>";
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
        if(isset($_COOKIE["userid"]))
        {
            echo "<h3>Chrip a message:</h3>
            <form method=\"post\" action=\"#\">
            <textarea name=\"content\" rows=\"4\" cols=\"50\"></textarea><br>
            <input type=\"hidden\" name=\"userid\" value=\"" . $_COOKIE["userid"] . "\">
            <input type=\"submit\" value=\"Post\">
            </form>";
            echo "<form method=\"post\" action=\"#\">
            Logout:<input type=\"submit\" value=\"Logout\" name=\"logout\">
            </form>";
        }
    ?>
</body>
</html>